<html>
<head>
<title>{$title}</title>
<link rel="stylesheet" type="text/css" href="{$subdir}media/style.css">
</head>
<body>
    
<div id="wrapper">
    
<div id="left">
    {if count($ric) >= 1}
        <div id="ric">
            {section name=ric loop=$ric}
                <p><a href="{$subdir}{$ric[ric].file}">{$ric[ric].name}</a></p>
            {/section}
        </div>
    {/if}
    
    {if $hastodos}
        <div id="todolist">
            <p><a href="{$subdir}{$todolink}">Todo List</a></p>
        </div>
    {/if}
    
    <b>Packages:</b>
    <br />
    
    {section name=packagelist loop=$packageindex}
        <a href="{$subdir}{$packageindex[packagelist].link}">
            {$packageindex[packagelist].title}
        </a>
        <br />
    {/section}
    
    <br /><br />
    
    {if $tutorials}
        <b>Tutorials/Manuals:</b>
        <br />
        
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
    {/if}
    
    {if !$noleftindex}{assign var="noleftindex" value=false}{/if}
    
    {if !$noleftindex}
        <!-- {if $compiledfileindex}
        <b>Files:</b><br />
          {eval var=$compiledfileindex}
          {/if} -->
        
        {if $compiledinterfaceindex}
            <b>Interfaces:</b><br />
              {eval var=$compiledinterfaceindex}
        {/if}
    
        {if $compiledclassindex}
            <b>Classes:</b><br />
              {eval var=$compiledclassindex}
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
    
