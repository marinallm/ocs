{**
 * header.tpl
 *
 * Copyright (c) 2000-2007 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site header.
 *
 * $Id$
 *}

{if !$pageTitleTranslated}{translate|assign:"pageTitleTranslated" key=$pageTitle}{/if}
{if $pageCrumbTitle}{translate|assign:"pageCrumbTitleTranslated" key=$pageCrumbTitle}{elseif !$pageCrumbTitleTranslated}{assign var="pageCrumbTitleTranslated" value=$pageTitleTranslated}{/if}
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset}" />
	<title>{$pageTitleTranslated}</title>
	<meta name="description" content="{$metaSearchDescription}" />
	<meta name="keywords" content="{$metaSearchKeywords}" />
	{$metaCustomHeaders}
	<link rel="stylesheet" href="{$baseUrl}/styles/common.css" type="text/css" />
	<link rel="alternate stylesheet" title="{translate key="icon.small.alt"}" href="{$baseUrl}/styles/small.css" type="text/css" />
	<link rel="stylesheet" title="{translate key="icon.medium.alt"}" href="{$baseUrl}/styles/medium.css" type="text/css" />
	<link rel="alternate stylesheet" title="{translate key="icon.large.alt"}" href="{$baseUrl}/styles/large.css" type="text/css" />
	{foreach from=$stylesheets item=cssUrl}
	<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
	{/foreach}
	<script type="text/javascript" src="{$baseUrl}/js/general.js"></script>
	{$additionalHeadData}
</head>
<body>
<div id="container">

<div id="header">
<div id="headerTitle">
<h1>
{if $displayPageHeaderLogo}
	<img src="{$publicConferenceFilesDir}/{$displayPageHeaderLogo.uploadName|escape:"url"}" width="{$displayPageHeaderLogo.width}" height="{$displayPageHeaderLogo.height}" border="0" alt="" />
{elseif $displayConferencePageHeaderLogo}
	<img src="{$publicConferenceFilesDir}/{$displayConferencePageHeaderLogo.uploadName|escape:"url"}" width="{$displayConferencePageHeaderLogo.width}" height="{$displayConferencePageHeaderLogo.height}" border="0" alt="" />
{/if}
{if $displayPageHeaderTitle && is_array($displayPageHeaderTitle)}
	<img src="{$publicConferenceFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}" width="{$displayPageHeaderTitle.width}" height="{$displayPageHeaderTitle.height}" border="0" alt="" />
{elseif $displayPageHeaderTitle}
	{$displayPageHeaderTitle}
{elseif $alternatePageHeader}
	{$alternatePageHeader}
{elseif $displayConferencePageHeaderTitle && is_array($displayConferencePageHeaderTitle)}
	<img src="{$publicConferenceFilesDir}/{$displayConferencePageHeaderTitle.uploadName|escape:"url"}" width="{$displayConferencePageHeaderTitle.width}" height="{$displayConferencePageHeaderTitle.height}" border="0" alt="" />
{elseif $displayConferencePageHeaderTitle}
	{$displayConferencePageHeaderTitle}
{elseif $alternateConferencePageHeader}
	{$alternateConferencePageHeader}
{elseif $siteTitle}
	{$siteTitle}
{else}
	{translate key="common.openConferenceSystems"}
{/if}
</h1>
</div>
</div>

<div id="body">

{display_template template="common/sidebar.tpl" hookname="Templates::Common::Header::sidebar"}

<div id="main">
<div id="navbar">
	<ul class="menu">
		<li><a href="{url schedConf=""}">{translate key="navigation.home"}</a></li>
		<li><a href="{url page="about"}">{translate key="navigation.about"}</a></li>
		{if $isUserLoggedIn}
			<li><a href="{url page="user"}">{translate key="navigation.userHome"}</a></li>
		{else}
			<li><a href="{url page="login"}">{translate key="navigation.login"}</a></li>
			<li><a href="{url page="user" op="account"}">{translate key="navigation.account"}</a></li>
		{/if}
		<li><a href="{url page="search"}">{translate key="navigation.search"}</a></li>
		{if $currentConference}
			{if $currentSchedConfsExist}<li><a href="{url schedConf="index" page="schedConfs" op="current"}">{translate key="navigation.current"}</a></li>{/if}
			{if $archivedSchedConfsExist}<li><a href="{url schedConf="index" page="schedConfs" op="archive"}">{translate key="navigation.archive"}</a></li>{/if}
			{if $enableAnnouncements}
				<li><a href="{url page="announcement"}">{translate key="announcement.announcements"}</a></li>
			{/if}
			{call_hook name="Templates::Common::Header::Navbar::CurrentConference"}
		{/if}
		{foreach from=$navMenuItems item=navItem}
			<li><a href="{if $navItem.isAbsolute}{$navItem.url|escape}{else}{$navItem.url|escape}{/if}">{if $navItem.isLiteral}{$navItem.name|escape}{else}{translate key=$navItem.name}{/if}</a></li>
		{/foreach}
	</ul>
</div>

<div id="breadcrumb">
	<a href="{url page="index"}">{translate key="navigation.home"}</a> &gt;
	{foreach from=$pageHierarchy item=hierarchyLink}
		<a href="{$hierarchyLink[0]}" class="hierarchyLink">{if not $hierarchyLink[2]}{translate key=$hierarchyLink[1]}{else}{$hierarchyLink[1]}{/if}</a> &gt;
	{/foreach}
	<a href="{$currentUrl}" class="current">{$pageCrumbTitleTranslated}</a>
</div>

<h2>{$pageTitleTranslated}</h2>

<div id="content">
