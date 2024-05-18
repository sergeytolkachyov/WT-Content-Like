<?php
/**
 * @package     WT Content like
 * @copyright   Copyright (C) 2023-2024 Sergey Tolkachyov. All rights reserved.
 * @author      Sergey Tolkachyov - https://web-tolk.ru
 * @link 		https://web-tolk.ru
 * @version 	2.0.0
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Plugin\Content\Wt_content_like\Extension;

use Joomla\CMS\Event\Content\AfterDisplayEvent;
use Joomla\CMS\Event\Content\AfterTitleEvent;
use Joomla\CMS\Event\Content\BeforeDisplayEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

defined('_JEXEC') or die;

final class Wt_content_like extends CMSPlugin implements SubscriberInterface
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentBeforeDisplay' => 'onContentBeforeDisplay',
            'onContentAfterDisplay' => 'onContentAfterDisplay',
            'onContentAfterTitle' => 'onContentAfterTitle',
            'onAjaxWt_content_like' => 'onAjaxWt_content_like',
        ];
    }

    /**
     * The display event.
     *
     * @param BeforeDisplayEvent $event The event object
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function onContentBeforeDisplay(BeforeDisplayEvent $event): void
    {

        $view = $this->getApplication()->getInput()->getString('view');

        if (
            ($view === 'article' && $this->params->get('button_like_article_position', 'before_display_content') == 'before_display_content')
            ||
            (($view === 'category' || $view === 'featured') && $this->params->get('button_like_category_position', 'before_display_content') == 'before_display_content')
        ) {
            $event->addResult($this->showLikeButton($event->getContext(), $event->getItem(), $event->getParams(), $event->getPage()));
        }


    }

    public function showLikeButton($context, $row, $params, $limitstart = 0): string
    {
        $parts = explode(".", $context);

        if ($parts[0] != 'com_content') {
            return false;
        }

        $html = '';

        /** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa = $this->getApplication()->getDocument()->getWebAssetManager();
        $wa->registerScript('wt_content_like', 'plg_content_wt_content_like/wt_content_like.js');
        $wa->useScript('wt_content_like');


        $category_exclude = $this->params->get('category_exclude');
        if (!is_array($category_exclude)) {
            $category_exclude = [];
        }

        if (!empty($params)
            //	&& $params->get('show_vote', null)
            && !in_array($row->catid, $category_exclude)
        ) {


            $view = $this->getApplication()->getInput()->getString('view', '');

            if ($context === 'com_content.article' && $view === 'article') {
                $displayData = [
                    'rating' => $row->rating,
                    'rating_count' => (int)$row->rating_count,
                    'css_btn_class' => $this->params->get('css_btn_class', 'btn btn-sm'),
                    'css_icon_class' => $this->params->get('css_icon_class', 'fas fa-thumbs'),
                    'css_badge_class' => $this->params->get('css_badge_class', 'badge bg-danger'),
                    'show_microdata' => $this->params->get('show_microdata', 0),
                    'article_id' => $row->id,
                ];

                $html .= $this->renderLikeButton($displayData);

            } elseif (($context === 'com_content.category' && $view === 'category') || $view === 'featured') {


                $model = $this->getApplication()->bootComponent('com_content')
                    ->getMVCFactory()
                    ->createModel('Article', 'Site', ['ignore_request' => false]);
                $model->setState('params', $params);

                $article = $model->getItem($row->id);
                $displayData = [
                    'rating' => $article->rating,
                    'rating_count' => $article->rating_count,
                    'css_btn_class' => $this->params->get('css_btn_class', 'btn btn-sm'),
                    'css_icon_class' => $this->params->get('css_icon_class', 'fas fa-thumbs'),
                    'css_badge_class' => $this->params->get('css_badge_class', 'badge bg-danger'),
                    'show_microdata' => $this->params->get('show_microdata', 0),
                    'article_id' => $row->id,
                ];

                $html .= $this->renderLikeButton($displayData);
            }
        }

        return $html;
    }

    public function renderLikeButton($displayData): string
    {
        $layoutId = $this->params->get('layout', 'default');
        $layout = new FileLayout($layoutId, JPATH_SITE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'wt_content_like' . DIRECTORY_SEPARATOR . 'tmpl');

        return $layout->render($displayData);
    }

    /**
     * The display event.
     *
     * @param AfterDisplayEvent $event The event object
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function onContentAfterDisplay(AfterDisplayEvent $event): void
    {
        $view = $this->getApplication()->getInput()->getString('view');

        if (
            ($view === 'article' && $this->params->get('button_like_article_position', 'after_display_content') == 'after_display_content')
            ||
            (($view === 'category' || $view === 'featured') && $this->params->get('button_like_category_position', 'after_display_content') == 'after_display_content')
        ) {
            $event->addResult($this->showLikeButton($event->getContext(), $event->getItem(), $event->getParams(), $event->getPage()));
        }

    }

    /**
     *
     * @param AfterTitleEvent $event
     *
     * @return void
     * @since 1.0.0
     */
    public function onContentAfterTitle(AfterTitleEvent $event): void
    {
        $view = $this->getApplication()->getInput()->getString('view');

        if (
            ($view === 'article' && $this->params->get('button_like_article_position', 'after_display_content') == 'after_display_title')
            ||
            (($view === 'category' || $view === 'featured') && $this->params->get('button_like_category_position', 'after_display_content') == 'after_display_title')
        ) {
            $event->addResult($this->showLikeButton($event->getContext(), $event->getItem(), $event->getParams(), $event->getPage()));
        }

    }

    public function onAjaxWt_content_like(Event $event): void
    {

        $data = $this->getApplication()->getInput()->getArray();
        $event->addResult($this->storeVote($data['article_id']));
    }


    public function storeVote($article_id): array
    {
        $model = $this->getApplication()->bootComponent('com_content')
            ->getMVCFactory()
            ->createModel('Article', 'Site', ['ignore_request' => false]);
        //Load com_content language constants
        $lang = $this->getApplication()->getLanguage();
        $extension = 'com_content';
        $base_dir = JPATH_SITE;
        $reload = true;
        $lang->load($extension, $base_dir, $lang->getTag(), $reload);

        if ($model->storeVote($article_id, 5)) {
            $model->setState('params', $this->getApplication()->getParams());
            $article = $model->getItem($article_id);

            return [
                'success' => 1,
                'message' => Text::_('COM_CONTENT_ARTICLE_VOTE_SUCCESS'),
                'rating' => $article->rating_count
            ];
        } else {
            return [
                'success' => 0,
                'message' => Text::_('COM_CONTENT_ARTICLE_VOTE_FAILURE')
            ];
        }
    }

}