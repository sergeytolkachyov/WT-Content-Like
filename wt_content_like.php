<?php
/**
 * @package    WT Content Like
 * @author     Sergey Tolkachyov info@web-tolk.ru https://web-tolk.ru
 * @copyright  Copyright (C) 2022 Sergey Tolkachyov. All rights reserved.
 * @license    GNU General Public License version 3 or later
 * @version	   1.0.0
 */
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

defined('_JEXEC') or die;


class PlgContentWt_content_like extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Displays the voting area if in an article
	 *
	 * @param   string    $context  The context of the content being passed to the plugin
	 * @param   object   &$row      The article object
	 * @param   object   &$params   The article params
	 * @param   integer   $page     The 'page' number
	 *
	 * @return  mixed  html string containing code for the votes if in com_content else boolean false
	 *
	 * @since   1.6
	 */
	public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
	{

		$parts = explode(".", $context);

		if ($parts[0] != 'com_content')
		{
			return false;
		}

		$html = '';

		$jversion = new JVersion();
		// only for Joomla 3.x
		if (version_compare($jversion->getShortVersion(), '4.0', '<')) {
			HTMLHelper::_('behavior.core');
			HTMLHelper::script('plg_content_wt_content_like/wt_content_like.js',[
				'version' => 'auto',
				'relative' => true]);

		} else {
			/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
			$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
			$wa->registerScript('wt_content_like', 'plg_content_wt_content_like/wt_content_like.js');
			$wa->useScript('wt_content_like');
		}

		$category_exclude = $this->params->get('category_exclude');
		if (!is_array($category_exclude))
		{
			$category_exclude = array();
		}

		if (!empty($params)
			//	&& $params->get('show_vote', null)
			&& !in_array($row->catid, $category_exclude)
		)
		{


			$view = Factory::getApplication()->input->getString('view', '');

			if ($context === 'com_content.article' && $view === 'article')
			{
				$displayData = [
					'rating'          => $row->rating,
					'rating_count'    => (int) $row->rating_count,
					'css_btn_class'   => $this->params->get('css_btn_class', 'btn btn-sm'),
					'css_icon_class'  => $this->params->get('css_icon_class', 'fas fa-thumbs'),
					'css_badge_class' => $this->params->get('css_badge_class', 'badge bg-danger'),
					'show_microdata'  => $this->params->get('show_microdata', 0),
					'article_id'      => $row->id,
				];

				$html .= $this->showLikeButton($displayData);

			}
			elseif ($context === 'com_content.category' && $view === 'category')
			{


				BaseDatabaseModel::addIncludePath('components/com_content/models', 'ContentModel');
				$model = BaseDatabaseModel::getInstance('Article', 'ContentModel', array('ignore_request' => true));
				$model->setState('params', $params);

				$article     = $model->getItem($row->id);
				$displayData = [
					'rating'          => $article->rating,
					'rating_count'    => $article->rating_count,
					'css_btn_class'   => $this->params->get('css_btn_class', 'btn btn-sm'),
					'css_icon_class'  => $this->params->get('css_icon_class', 'fas fa-thumbs'),
					'css_badge_class' => $this->params->get('css_badge_class', 'badge bg-danger'),
					'show_microdata'  => $this->params->get('show_microdata', 0),
					'article_id'      => $row->id,
				];

				$html .= $this->showLikeButton($displayData);
			}
		}

		return $html;
	}

	public function showLikeButton($displayData)
	{
		$layoutId = $this->params->get('layout', 'default');
		$layout   = new FileLayout($layoutId, JPATH_SITE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'wt_content_like' . DIRECTORY_SEPARATOR . 'tmpl');

		return $layout->render($displayData);
	}


	public function onAjaxWt_content_like()
	{

		$data = Factory::getApplication()->input->getArray();

		return $this->storeVote($data['article_id']);

	}


	public function storeVote($article_id)
	{

		BaseDatabaseModel::addIncludePath('components/com_content/models', 'ContentModel');
		$model = BaseDatabaseModel::getInstance('Article', 'ContentModel', array('ignore_request' => true));
		//Load com_content language constants
		$lang      = Factory::getLanguage();
		$extension = 'com_content';
		$base_dir  = JPATH_SITE;
		$reload    = true;
		$lang->load($extension, $base_dir, $lang->getTag(), $reload);

		if ($model->storeVote($article_id, 5))
		{
			$model->setState('params', Factory::getApplication()->getParams());
			$article = $model->getItem($article_id);

			return json_encode(
				array(
					'success' => 1,
					'message' => Text::_('COM_CONTENT_ARTICLE_VOTE_SUCCESS'),
					'rating'  => $article->rating_count
				)
			);
		}
		else
		{
			return json_encode(array('success' => 0, 'message' => Text::_('COM_CONTENT_ARTICLE_VOTE_FAILURE')));
		}
	}

}