<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2007 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */

/**
 * class lmbMacroWrapTag.
 *
 * @tag insert
 * @aliases wrap,include
 * @req_attributes file
 * @package macro
 * @version $Id$
 */
class lmbMacroInsertTag extends lmbMacroTag
{
  protected $is_dynamic = false;

  function preParse($compiler)
  {
    if($this->has('in'))
      $this->set('into', $this->get('in'));

    if($this->has('with')) // for BC with old {{wrap}} tag
    {
      $att = $this->getAttributeObject('with');
      $att->setName('file');
      $this->add($att);
    }

    parent :: preParse($compiler);
    
    if($this->isDynamic('file'))
      $this->is_dynamic = true;

    if(!$this->is_dynamic)
    {
      $file = $this->get('file');
      $compiler->parseTemplate($file, $this);

      //if there's no 'into' attribute we consider that {{insert:into}} tags used instead
      if($into = $this->get('into'))
      {
        $tree_builder = $compiler->getTreeBuilder();
        $this->_insert($this, $tree_builder, $into);
      }
    }
  }

  function _insert($wrapper, $tree_builder, $point)
  {
    $insertionPoint = $wrapper->findChild($point);
    if(empty($insertionPoint))
    {
      $params = array('slot' => $point);
      if($wrapper !== $this)
      {
        $params['parent_wrap_tag_file'] = $wrapper->getTemplateFile();
        $params['parent_wrap_tag_line'] = $wrapper->getTemplateLine();
      }

      $this->raise('Wrap slot not found', $params);
    }

    $tree_builder->pushCursor($insertionPoint, $this->location);
  }

  protected function _collectIntos()
  {
    return $this->findChildrenByClass('lmbMacroInsertIntoTag');
  }

  protected function _generateContent($code)
  {
    if($this->is_dynamic)
      $this->_generateDynamicaly($code);
    else
      $this->_generateStaticaly($code);
  }

  function _generateDynamicaly($code)
  {
    $handlers_str = 'array(';
    $methods = array();
    
    if($this->getBool('inline'))
      $this->raise('Inline is not supported for dynamic case');      

    //collecting {{into}} tags
    if($intos = $this->_collectIntos())
    {
      foreach($intos as $into)
      {
        $args = $code->generateVar(); 
        $methods[$into->get('slot')] = $code->beginMethod('__slotHandler'. uniqid(), array($args . '= array()'));
        $code->writePHP("if($args) extract($args);"); 
        $into->generateNow($code);
        $code->endMethod();
      }
    }
    elseif($this->has('into'))
    {
      $args = $code->generateVar(); 
      $methods[$this->get('into')] = $code->beginMethod('__slotHandler'. uniqid(), array($args . '= array()'));
      $code->writePHP("if($args) extract($args);"); 
      parent :: _generateContent($code);
      $code->endMethod();
    }

    foreach($methods as $slot => $method)
      $handlers_str .= '"' . $slot . '"' . ' => array($this, "' . $method . '"),';

    $handlers_str .= ')';

    $arg_str = $this->attributesIntoArrayString();

    $code->writePHP('$this->includeTemplate(' . $this->get('file') . ', ' . $arg_str . ','. $handlers_str . ');');
  }  
  
  function _generateStaticaly($code)
  {
    if($this->getBool('inline'))
      parent :: _generateContent($code);
    else
    {
      static $counter = 1;
  
      list($keys, $vals) = $this->attributesIntoArgs();
  
      $method = $code->beginMethod('__staticInclude' . ($counter++), $keys);
      parent :: _generateContent($code);
      $code->endMethod();
  
      $code->writePHP('$this->' . $method . '(' . implode(', ', $vals) . ');');
    }
  }
}

