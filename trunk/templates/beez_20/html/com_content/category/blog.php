<?php
/**
 * @version                $Id: blog.php 13151 2009-10-11 17:10:52Z severdia changed a.radtke  $
 * @package                Joomla.Site
 * @subpackage        com_content
 * @copyright        Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license                GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers');
$cparams =& JComponentHelper::getParams('com_media');

// If the page class is defined, add to class as suffix.
// It will be a separate class if the user starts it with a space
$pageClass = $this->params->get('pageclass_sfx');
?>

<section class="blog<?php echo $pageClass;?>">

<?php if ($this->params->get('show_page_title', 1)) : ?>
<h1>
        <?php echo $this->escape($this->params->get('page_title')); ?>
</h1>
<?php endif; ?>


<?php if ($this->params->get('show_description', 1) || $this->params->def('show_description_image', 1)) :?>
<details class="category_description">
        <?php if ($this->params->get('show_description_image') && $this->category->image) : ?>
                <img src="<?php echo $this->baseurl . '/' . $cparams->get('image_path') . '/'. $this->category->image;?>" align="<?php echo $this->category->image_position;?>" hspace="6" alt="" />
        <?php endif; ?>
        <?php if ($this->params->get('show_description') && $this->item->description) : ?>
                <?php echo $this->item->description; ?>
        <?php endif; ?>
</details>
<?php endif; ?>




<ul class="jsubcategories">
        <?php foreach($this->children as $child) : ?>

                        <li><a href="<?php /* @TODO class not found echo JRoute::_(ContentHelperRoute::getCategoryRoute($child->id)); */ ?>">
                                <?php echo $child->title; ?></a> (<?php /* echo @TODO numitems not loaded $child->numitems; */?>)</li>
        <?php endforeach; ?>
</ul>




<?php $i=0; ?>
<?php if (!empty($this->lead_items)) : ?>
<div class="leading">
        <?php foreach ($this->lead_items as &$item) : ?>
        <article <?php echo $item->state == 0 ? ' class="system-unpublished"' : null; ?>>
                <?php
                        $this->item = &$item;
                        $i++;
                        echo $this->loadTemplate('item');
                ?>
        </article>
        <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($this->intro_items)) : ?>


        <?php
        	$introcount = $this->params->def('num_intro_articles', 4);
        	$realintrocounter = count($this->intro_items);
        	if ($realintrocounter < $introcount) $introcount = $realintrocounter;

        	if ($introcount) :
                $colcount = $this->params->def('num_columns', 2);

                if ($colcount == 0) :
                        $colcount = 1;
                endif;

                $rowcount = (int) (($introcount / $colcount) + 0.5);

                for ($y = 0; $y < $rowcount; $y++) : ?>
                	<div class="article_row<?php echo $this->escape($this->params->get('pageclass_sfx'));  echo ' row'.$y ;?>">
                    <?php for ($z = 0; $z < $colcount ; $z++  ) : ?>
                    		<?php
                    				if (isset($this->intro_items[$i])) :?>
                    					<article class="article_column column<?php echo $z + 1; ?> cols<?php echo $colcount; ?>" >
                                     	<?php
									  		$this->item=$this->intro_items[$i];
									  		$i++;
                                      		echo $this->loadTemplate('item');
                                     	?>
                                     	</article>
                                     	<span class="article_separator">&nbsp;</span>
                                     <?php else :  ?>

                                     <?php endif;  ?>

                                <?php endfor; ?>
                                <span class="row_separator<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">&nbsp;</span>
                        </div>
                <?php endfor;
        endif; ?>
<?php endif; ?>

<?php if (!empty($this->link_items)) : ?>
        <div class="blog_more">
        <?php echo $this->loadTemplate('links'); ?>
        </div>
<?php endif; ?>




<?php  /* if ($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2 && $this->pagination->get('pages.total') > 1)) : ?>
        <div class="pagination">
                <?php // echo $this->pagination->getPagesLinks(); ?>
                <?php // if ($this->params->def('show_pagination_results', 1)) : ?>
                        <p class="counter">
                                <?php // echo $this->pagination->getPagesCounter(); ?>
                        </p>

                <?php // endif; ?>
                   <?php // echo $this->pagination->getPagesLinks(); ?>
        </div>
<?php  endif;  */ ?>



</section>
