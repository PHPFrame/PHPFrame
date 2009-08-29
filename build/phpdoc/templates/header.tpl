<html>
<head>
<title>{$title}</title>
<meta name="generator" content="PHPDoc">
<meta http_equiv="Content-Type" content="text/html; charset=UTF-8">
<base href="http://www.phpframe.org/doc/api/">
<link rel="stylesheet" href="media/styles.css" type="text/css">
<link rel="stylesheet" href="http://www.phpframe.org/themes/phpframe.org/css/styles.css" type="text/css">
<script type="text/javascript" src="http://www.phpframe.org/lib/jquery/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="http://www.phpframe.org/lib/jquery/js/jquery-ui-1.7.custom.min.js"></script>
<script type="text/javascript" src="http://www.phpframe.org/themes/phpframe.org/js/twitter.js"></script>
</head>
<body>
    
<!-- ******************** start #wrapper ******************** -->
<div id="wrapper">

<!-- ******************** start #header ******************** -->
<div id="header">
    
<h1><a href="http://www.phpframe.org/">PHPFrame.org</a></h1>
    
<!-- ******************** start #twitter-box ******************** -->
<div id="twitter-box">

<div id="tweetdate"></div>
<div id="tweetcontent"></div>

<a href="http://www.twitter.com/phpframe" title="Follow us on Twitter">
    <img src="themes/phpframe.org/images/twitter.png" 
         alt="Twitter Logo" />

</a>

<div style="clear: both;"></div>
    
</div>
<!-- ******************** end #twitter-box ******************** -->

<div style="clear: both;"></div>

<!-- ******************** start #topmenu ******************** -->
<div id="topmenu">

<ul>
    <li>
        <a href="http://www.phpframe.org/content/index">Home</a>
    </li>
    <li>
        <a href="http://www.phpframe.org/content/download">Download</a>
    </li>
    <li>
        <a href="http://www.phpframe.org/content/doc">Documentation</a>
    </li>
    <li>
        <a href="http://www.phpframe.org/content/tutorials">Tutorials</a>
    </li>
    <li>
        <a href="http://www.phpframe.org/content/tracker">Bug tracker</a>
    </li>
    <li>
        <a href="http://www.phpframe.org/content/discussion">Discussion</a>
    </li>
</ul>

</div>
<!-- ******************** end #topmenu ******************** -->

</div>
<!-- ******************** end #header ******************** -->

<div style="clear: both;"></div>


<!-- ******************** start #content ******************** -->
<div id="content" class="doc-index">

<div class="pathway">
    <span class="pathway_item">
        <a href="http://www.phpframe.org/index.php">Home</a>
    </span>
     &gt;&gt; 
    <span class="pathway_item">
        API Documentation
    </span>
</div>

<h2>API Documentation</h2>
<!-- End top chunk copied from main site -->


    
<div id="left">

{if count($ric) >= 1}
<div class="sidebar ric">

<ul>
{section name=ric loop=$ric}
    <li class="textfile"><a href="{$subdir}{$ric[ric].file}">{$ric[ric].name}</a></li>
{/section}
</ul>

</div><!-- end ric .sidebar -->
{/if}
    
{if $hastodos}
<div class="sidebar todolist">
    <a href="{$subdir}{$todolink}">Todo List</a>
</div><!-- end todos .sidebar -->
{/if}

<div class="sidebar packages">

<h3>Packages:</h3>

<ul>
{section name=packagelist loop=$packageindex}
    <li>
	    <a href="{$subdir}{$packageindex[packagelist].link}">
	        {$packageindex[packagelist].title}
	    </a>
    </li>
{/section}
</ul>

</div><!-- end packages .sidebar -->


{if $tutorials}
<div class="sidebar tutorials">

<h3>Tutorials/Manuals:</h3>

{if $tutorials.pkg}
    <strong>Package-level:</strong>
    {section name=ext loop=$tutorials.pkg}
        {$tutorials.pkg[ext]}
    {/section}
{/if}

{if $tutorials.cls}
    <strong>Class-level:</strong>
    {section name=ext loop=$tutorials.cls}
        {$tutorials.cls[ext]}
    {/section}
{/if}

{if $tutorials.proc}
    <strong>Procedural-level:</strong>
    {section name=ext loop=$tutorials.proc}
        {$tutorials.proc[ext]}
    {/section}
{/if}

</div><!-- end .tutorials .sidebar -->
{/if}

{if !$noleftindex}{assign var="noleftindex" value=false}{/if}

{if !$noleftindex}

<!--
{if $compiledfileindex}
<div class="sidebar files">
    <h3>Files:</h3>
    {eval var=$compiledfileindex}
</div>
{/if}
-->

{if $compiledinterfaceindex}
<div class="sidebar interfaces">
    <h3>Interfaces:</h3>
      {eval var=$compiledinterfaceindex}
</div><!-- end .interfaces .sidebar -->
{/if}

{if $compiledclassindex}
<div class="sidebar classes">
    <h3>Classes:</h3>
      {eval var=$compiledclassindex}
</div><!-- end .classes .sidebar -->
{/if}


{/if}

</div><!-- close #left -->

<div id="main_col">

{assign var="packagehaselements" value=false}

{foreach from=$packageindex item=thispackage}
    {if in_array($package, $thispackage)}
        {assign var="packagehaselements" value=true}
    {/if}
{/foreach}

{if $packagehaselements}
    [ <a href="{$subdir}classtrees_{$package}.html" class="menu">class tree: {$package}</a> ]
    [ <a href="{$subdir}elementindex_{$package}.html" class="menu">index: {$package}</a> ]
{/if}

[ <a href="{$subdir}elementindex.html" class="menu">all elements</a> ]
  
<hr />

{if !$hasel}{assign var="hasel" value=false}{/if}

{if $hasel}
    <h1 style="text-align: left;">{$eltype|capitalize}: {$class_name}</h1>
    Source Location: {$source_location}
    <br /><br />
{/if}
    
