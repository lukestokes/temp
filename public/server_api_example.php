<?php
$input = file_get_contents("php://input");
if ($_SERVER['REQUEST_METHOD'] == 'GET' && $input == '') {
    print '<helpdesk
	xmlns="urn:org.restfest.2012.hackday.helpdesk"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:xhtml="http://www.w3.org/1999/xhtml"
>
	<atom:link rel="self" href="http://.../" type="application/vnd.org.restfest.2012.hackday+xml"/>
	
	<atom:link rel="http://helpdesk.hackday.2012.restfest.org/rels/tickets" type="application/vnd.org.restfest.2012.hackday+xml" href="http://.../tickets/" />
	<xhtml:form rel="http://helpdesk.hackday.2012.restfest.org/rels/tickets" type="application/vnd.org.restfest.2012.hackday+xml" action="http://.../tickets/" method="get">
		<xhtml:select name="sort_field">
			<xhtml:option value="created_at" selected="selected">created_at</xhtml:option>
			<xhtml:option value="updated_at">updated_at</xhtml:option>
		</xhtml:select>
		<xhtml:select name="sort_order">
			<xhtml:option value="asc">asc</xhtml:option>
			<xhtml:option value="desc" selected="selected">desc</xhtml:option>
		</xhtml:select>
		<xhtml:input type="number" name="result_size" value="20" />
		<xhtml:input type="number" name="result_page" value="1" />
	</xhtml:form>
	
	<atom:link rel="http://helpdesk.hackday.2012.restfest.org/rels/users" type="application/vnd.org.restfest.2012.hackday+xml" href="http://.../users/" />
	<xhtml:form rel="http://helpdesk.hackday.2012.restfest.org/rels/users" type="application/vnd.org.restfest.2012.hackday+xml" action="http://.../users/" method="get">
		<xhtml:input name="user_name" type="text" />
		<xhtml:input name="user_email" type="text" />
		<xhtml:select name="sort_field">
			<xhtml:option value="name" selected="selected">name</xhtml:option>
			<xhtml:option value="email">email</xhtml:option>
		</xhtml:select>
		<xhtml:select name="sort_order">
			<xhtml:option value="asc" selected="selected">asc</xhtml:option>
			<xhtml:option value="desc">desc</xhtml:option>
		</xhtml:select>
		<xhtml:input type="number" name="result_size" value="20" />
		<xhtml:input type="number" name="result_page" value="1" />
	</xhtml:form>
	
	<atom:link rel="http://helpdesk.hackday.2012.restfest.org/rels/changes" href="http://.../feed" type="application/vnd.org.restfest.2012.hackday+xml" />
	<xhtml:form rel="http://helpdesk.hackday.2012.restfest.org/rels/changes" action="http://.../feed" type="application/vnd.org.restfest.2012.hackday+xml" method="get">
		<input type="datetime" name="from" />
		<input type="datetime" name="to" />
	</xhtml:form>
</helpdesk>';
}
//trigger_error("\n\nI'm a SERVER! I was told to do this via a SYNC!" . $input->asXML() . "\n\n");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Location: http://.../tickets/9172361_sync_server');
    http_response_code(201);
}
