<html>
<head>
<title>{$title}</title>
<link rel="stylesheet" type="text/css" href="{$subdir}media/style.css">
</head>
<body>
    
<div id="wrapper">
    
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
    
