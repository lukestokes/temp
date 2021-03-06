<?php
include 'CurlClient.php';
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
	
	<event timestamp="2012-09-13T12:01:07Z" type="ticket_deletion">
		<atom:link rel="http://helpdesk.hackday.2012.restfest.org/rels/ticket" href="http://.../tickets/71263471" type="application/vnd.org.restfest.2012.hackday+xml" />
	</event>
	<event timestamp="2012-09-13T12:01:24Z" type="ticket_update">
		<ticket:ticket>
			<atom:link rel="self" href="http://.../tickets/9172361" type="application/vnd.org.restfest.2012.hackday+xml" />
			<summary>New title :)</summary>
			<!-- all other fields here too -->
		</ticket:ticket>
	</event>
	<event timestamp="2012-09-13T12:01:49Z" type="comment_creation">
		<comment:comment>
			<atom:link rel="self" href="http://.../tickets/9172361/comments/askjklz1287a" type="application/vnd.org.restfest.2012.hackday+xml" />
			<atom:link rel="http://helpdesk.hackday.2012.restfest.org/rels/ticket" href="http://.../tickets/9172361" type="application/vnd.org.restfest.2012.hackday+xml" />
			<comment:created_at>2012-09-13T12:01:49Z</comment:created_at>
			<comment:author>
				<comment:name>Joe Cool</comment:name>
				<comment:email>snoopy@peanuts.com</comment:email>
			</comment:author>
			<comment:body>I really like this idea</comment:body>
		</comment:comment>
	</event>
	
	<xhtml:form rel="http://helpdesk.hackday.2012.restfest.org/rels/changes" action="http://.../feed" type="application/vnd.org.restfest.2012.hackday+xml" method="get">
		<input type="datetime" name="from" />
		<input type="datetime" name="to" />
	</xhtml:form>
</changes>';

$client = new CurlClient();
$uri = 'https://admin-luke.foxycart.com/temp/webhook_endpoint.php';
$response = $client->post($uri,$xml);
var_dump($response['status']);
