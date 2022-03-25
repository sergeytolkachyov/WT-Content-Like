<?php
/**
 * @package    WT Content Like
 * @author     Sergey Tolkachyov info@web-tolk.ru https://web-tolk.ru
 * @copyright  Copyright (C) 2022 Sergey Tolkachyov. All rights reserved.
 * @license    GNU General Public License version 3 or later
 * @version	   1.0.0
 */
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');
/**
 * @var $displayData array Digital sign data
 * Use
 *      echo '<pre>';
 *        print_r($displayData);
 *        echo '</pre>';
 *
 */

//echo '<pre>';
//print_r($displayData);
//echo '</pre>';
?>

<?php if ($displayData['show_microdata'] == 1 && $displayData['rating_count'] > 0): ?>
	<div class="content_rating" style="display:none;" itemprop="aggregateRating" itemscope
		 itemtype="http://schema.org/AggregateRating">
		<p class="unseen element-invisible">
			<span itemprop="ratingValue"><?php echo $displayData['rating']; ?></span>
			<span itemprop="bestRating">5</span>
			<meta itemprop="ratingCount" content="<?php echo $displayData['rating_count']; ?>"/>
			<meta itemprop="worstRating" content="0"/>
		</p>
	</div>
<?php endif; ?>
<button type="button" name="submit_vote" data-article-id="<?php echo $displayData['article_id']; ?>" class="wt_content_like_btn <?php echo $displayData['css_btn_class']; ?>">
	<i class="<?php echo $displayData['css_icon_class']; ?>"></i>


		<span id="wt_content_like_rating_count_<?php echo $displayData['article_id']; ?>" class="rating_count <?php echo $displayData['css_badge_class']; ?>"
			<?php if (!$displayData['rating_count'] && $displayData['rating_count'] == 0): ?>
				style="display:none;"
			<?php endif; ?>
		>

			<?php echo $displayData['rating_count']; ?>
		</span>


</button>
<span id="wt_content_like_meesage_<?php echo $displayData['article_id']; ?>" style="display: none;"></span>

