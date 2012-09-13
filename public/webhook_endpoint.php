<?php
include 'CurlClient.php';

/**
 *
 * TABLES:
 *
 * log
 *     id
 *     notification
 *     result
 *     created_at
 *
 *
 */
// get config from a database?
// twig translation here?
$server_config = array(
        'name' => 'My Cool Helpdesk Server',
        'api_home_url' => 'https://admin-luke.foxycart.com/temp/server_api_example.php',
);




$client = new CurlClient();
$debug = 1;
//$xml = file_get_contents("php://input");

$xml = '<changes
xmlns="urn:org.restfest.2012.hackday.helpdesk.feed"
xmlns:ticket="urn:org.restfest.2012.hackday.helpdesk.ticket"
xmlns:comment="urn:org.restfest.2012.hackday.helpdesk.comment"
xmlns:atom="http://www.w3.org/2005/Atom">

<atom:link rel="prev" type="application/vnd.org.restfest.2012.hackday+xml" href="http://../feed?from=2012-09-13T12:00:00Z&amp;to=2012-09-13T12:00:59Z" />
<atom:link rel="self" type="application/vnd.org.restfest.2012.hackday+xml" href="http://../feed?from=2012-09-13T12:01:00Z&amp;to=2012-09-13T12:01:59Z" />
<atom:link rel="next" type="application/vnd.org.restfest.2012.hackday+xml" href="http://../feed?from=2012-09-13T12:02:00Z&amp;to=2012-09-13T12:02:59Z" />

<from>2012-09-13T12:01:00Z</from>
<to>2012-09-13T12:01:59Z</to>

<event timestamp="2012-09-13T12:01:24Z" type="ticket_creation">
<ticket:ticket>
	<atom:link rel="self" href="http://.../tickets/9172361" type="application/vnd.org.restfest.2012.hackday+xml" />
	
	<ticket:created_at>2012-09-13T12:03:00Z</ticket:created_at>
	<ticket:updated_at>2012-09-13T12:03:00Z</ticket:updated_at>
	
	<ticket:summary>Running out of coffee</ticket:summary>
	
	<ticket:description>It appears that we\'ll be running out of coffee any moment.

I\'m worried that we might have a full blown riot on our hands if that happens.</ticket:description>
	
	<ticket:author>
		<user:user>
			<name>David Zuelke</name>
			<email>david.zuelke@bitextender.com</email>
		</user:user>
	</ticket:author>
	
	<ticket:assignee>
		<user:user>
			<atom:link rel="self" href="http://.../users/817236781" type="application/vnd.org.restfest.2012.hackday+xml" />
			<name>Benjamin Young</name>
		</user:user>
	</ticket:assignee>
	
	<ticket:tag>restfest</ticket:tag>
	<ticket:tag>breakfast</ticket:tag>
	<ticket:tag>coffee</ticket:tag>
	<ticket:tag>catering</ticket:tag>
	
	<ticket:state>open</ticket:state>
	
	<!-- <atom:link rel="http://helpdesk.hackday.2012.restfest.org/rels/ticket/state" href="http://.../tickets/9172361/state" type="text/plain" /> -->
	
	<comments:comments count="2">
		<atom:link rel="http://helpdesk.hackday.2012.restfest.org/rels/comments" href="http://.../tickets/9172361/comments" type="application/vnd.org.restfest.2012.hackday.helpdesk+xml" />
	</comments:comments>
</ticket>

<xhtml:form rel="http://helpdesk.hackday.2012.restfest.org/rels/changes" action="http://.../feed" type="application/vnd.org.restfest.2012.hackday+xml" method="get">
<input type="datetime" name="from" />
<input type="datetime" name="to" />
</xhtml:form>
</changes>';



if ($debug) {
    //trigger_error(serialize($xml));
}
$xml = simplexml_load_string($xml, NULL, LIBXML_NOCDATA);
if ($debug) {
    //trigger_error(serialize($xml->asXML()));
}
$xml->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');
$xml->registerXPathNamespace('ticket', 'urn:org.restfest.2012.hackday.helpdesk.ticket');
$xml->registerXPathNamespace('user', 'urn:org.restfest.2012.hackday.helpdesk.user');
$xml->registerXPathNamespace('comment', 'urn:org.restfest.2012.hackday.helpdesk.comment');
$xml->registerXPathNamespace('comments', 'urn:org.restfest.2012.hackday.helpdesk.comments');

foreach($xml->event as $event) {
    if ($debug) {
        // trigger_error(serialize($event->asXML()));
    }
    switch((string)$event->attributes()->type) {
        case 'ticket_creation':
            // find where to post tickets
            $url = '';
            $response = $client->get($server_config['api_home_url']);
            if (substr($response['status'], 1) == '4' || substr($response['status'], 1) == '5') {
                logError('api_home_url ERROR',$response['body']);
            } elseif ($response['error'] != 0) {
                logError('api_home_url CONNECTION ERROR',$response['error_msg']);
            } else {
                $home_xml = simplexml_load_string($response['body'], NULL, LIBXML_NOCDATA);
                $atom_link = $event->xpath("atom:link[@rel='http://helpdesk.hackday.2012.restfest.org/rels/ticket']");
                $url = (string)$atom_link[0]->attributes()->href;
                
                var_dump($url);
                
            }
            $node = $event->xpath("ticket:ticket");
            $xml = $node[0]->asXML();
            
            // TODO: fix self link
            
            $response = $client->post($url,$xml);
            if (substr($response['status'], 1) == '4' || substr($response['status'], 1) == '5') {
                logError('SYNC ERROR',$response['body']);
            } elseif ($response['error'] != 0) {
                logError('SYNC CONNECTION ERROR',$response['error_msg']);
            } else {
                $sync_server_uri = $response['headers']['location'];
                
                var_dump($sync_server_uri);
                
                
                saveUrlMapping($master_server_url,$sync_server_uri);
                logEvent($event);
            }
            break;
        case 'ticket_update':
            $node = $event->xpath("ticket:ticket");
            
            // TODO: fix self link
            
            $xml = $node[0]->asXML();
            $atom_link = $node[0]->xpath("atom:link[@rel='self']");
            $url = (string)$atom_link[0]->attributes()->href;
            $mapped_url = getUrlMapping($url);
            if ($mapped_url) {
                $response = $client->put($url,$xml);
                if (substr($response['status'], 1) == '4' || substr($response['status'], 1) == '5') {
                    logError('SYNC ERROR',$response['body']);
                } else {
                    logEvent($event);
                }
            } else {
                logError('MAPPING','No mapping found for ' . $url . '. Skipping.');
            }
            break;
        case 'ticket_deletion':
            $atom_link = $event->xpath("atom:link[@rel='http://helpdesk.hackday.2012.restfest.org/rels/ticket']");
            $url = (string)$atom_link[0]->attributes()->href;
            $mapped_url = getUrlMapping($url);
            if ($mapped_url) {
                $response = $client->delete($mapped_url);
                if (substr($response['status'], 1) == '4' || substr($response['status'], 1) == '5') {
                    logError('SYNC ERROR',$response['body']);
                } elseif ($response['error'] != 0) {
                    logError('CONNECTION ERROR',$response['error_msg']);
                } else {
                    removeUrlMapping($url);
                    logEvent($event);
                }
            } else {
                logError('MAPPING','No mapping found for ' . $url . '. Skipping.');
            }
            break;
            /*
        case 'comment_creation':
        case 'comment_update':
            $node = $event->xpath("comment:comment");
            $xml = $node[0]->asXML();
            $atom_link = $node[0]->xpath("atom:link[@rel='self']");
            $url = (string)$atom_link[0]->attributes()->href;
            $response = $client->put($url,$xml);
            if (substr($response['status'], 1) == '4' || substr($response['status'], 1) == '5') {
                logError('SYNC ERROR',$response['body']);
            } elseif ($response['error'] != 0) {
                logError('CONNECTION ERROR',$response['error_msg']);
            } elseif ($response['error'] != 0) {
                logError('CONNECTION ERROR',$response['error_msg']);
            } else {
                logEvent($event);
            }
            break;
        case 'comment_update':
            break;
        case 'comment_deletion':
            $atom_link = $event->xpath("atom:link[@rel='http://helpdesk.hackday.2012.restfest.org/rels/comment']");
            $url = (string)$atom_link[0]->attributes()->href;
            $response = $client->delete($url);
            if (substr($response['status'], 1) == '4' || substr($response['status'], 1) == '5') {
                logError('SYNC ERROR',$response['body']);
            } elseif ($response['error'] != 0) {
                logError('CONNECTION ERROR',$response['error_msg']);
            } else {
                logEvent($event);
            }
            break;
            */
    }
}

function logEvent($event)
{
    // log event
}

function logError($error_code, $error_message)
{
    trigger_error($error_code . ': ' . $error_message);
    var_dump($error_code . ': ' . $error_message);
}
function saveUrlMapping($source,$destination)
{
    return apc_store($source,$destination);
}
function getUrlMapping($source)
{
    return apc_fetch($source);
}
function removeUrlMapping($source)
{
    return apc_delete($source);
}
