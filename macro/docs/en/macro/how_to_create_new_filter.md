# How to create your own {{macro}} filters
macro filters are stored in files with .filter.php suffix in file name. Filters files can be found in limb/macro/src/filters/ folder and also in src/macro folders of Limb3 packages and Limb-based applications.

## Filters core classes
You can use the following classes as a base for your filter:

* **lmbMacroFunctionBasedFilter** — creates a macro filter that will apply generic PHP function or any other function to the value. The examples of such filters are: html, nl2br, trim and others. In the simplest form you just need to inherit from lmbMacroFunctionBasedFilter and to specify the function name.
* **lmbMacroFilter** — generic class for filters. Can be used in more complex cases.

## Filter annotations
In the head of any filter class file there is an annotation section, for example:

    <?php 
    /**
     * @filter number_format
     * @aliases number
     */ 
    class lmbMacroNumberFormatFilter extends lmbMacrounctionBasedFilter
    {
      [..]
    }

The following annotations currently can be used for filters:

* filter — main filter name.
* aliases — other filter names. Several aliases separated with commas.

## Creating a filter based on lmbMacroFunctionBasedFilter
Let's consider an example of creating a macro filter that applies some **quote($sting)** function. We'd like to use this filter in a template as follows: {$var|quote}.

Let's call our filer as **QuoteFilter**. Here is the source code for this filter:

    <?php 
    /**
     * @filter quote
     */ 
    class QuoteFilter extends lmbMacroFunctionBasedFilter
    {
      protected $function = 'quote';
    }

And that's it!

As a result macro will compile expression {$title|quote} into `<?php echo quote($title); ?>`

lmbMacroFunctionBasedFilter automatically will pass any extra arguments into the specified function in the same order as in template. For example, the expression {$my_number|number_format:2,$del} will be compiled into the following PHP code:

    number_format($title, 2, $del);

You may also want to use **$include_file attribute** of lmbMacroFunctionBasedFilter in order to require PHP file with function declaration:

    <?php 
    /**
     * @filter quote
     */ 
    class QuoteFilter extends lmbMacroFunctionBasedFilter
    {
      protected $function = 'quote';
      protected $include_file = '/path/to/quote_function_declaration.php';
    }  
    ?>

## Creating more complex filter
Any filter has a reference to so called **$base**. **$base** — is either an object of lmbMacroExpressionNode class or other filter in case if two or more filters applied to an expression. **$base** supports **getValue()** that returns a piece of PHP code with $base value.

Let's take a look at **date** filter that can be found in limb/macro/src/filters/core/date.filter.php. Although date() is just a regular PHP function we can't use lmbMacroFunctionBasedFilter as a parent class in this case since date() accepts $format argument first. That's why we inherited from lmbMacroFilter:

    /**
     * class lmbMacroDateFilter.
     *
     * @filter date
     * @package macro
     * @version $Id$
     */ 
    class lmbMacroDateFilter extends lmbMacroFilter
    {
      function getValue()
      {
        return 'date(' . $this->params[0].', ' . $this->base->getValue() . ')';
      }  
    } 

Filter params are available by **$params** attribute. Parameters don't have any names so we have to reference them by index. Parameters are stored as is that's why you don't need to escape them while writing the compiled template. For example, $this→params[0] for expression like {$var|trim:»&»} will be exactly **»&«**.
