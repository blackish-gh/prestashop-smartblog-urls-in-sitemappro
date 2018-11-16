<?php
/**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA    <contact@prestashop.com>
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class SitemapBuilder
{
	public $link;
	private static $instance = null;

	public function __construct()
	{
		if (version_compare(_PS_VERSION_, '1.7', '<'))
			$this->link = new LinkSMP();
		else
			$this->link = new LinkSMPPS7();
	}

	protected function __clone()
	{
	}

	public static function getInstance()
	{
		if (is_null(self::$instance))
			self::$instance = new self();
		return self::$instance;
	}

	public function generate($id_lang = null, $with_image = false, $with_link = false)
	{
		if ($with_link && is_null($id_lang))
			$id_lang = Context::getContext()->language->id;

		$sitemap = new Sitemap(ToolsSMP::getShopDomain());
		$sitemap->setPath(_PS_ROOT_DIR_.'/');
		if ($with_link)
			$sitemap->setIncludeLinks(true);
		$sitemap->setFilename(SitemapConfig::getSitemapFilename($id_lang, true, $with_image, $with_link));

		$categories = array();
		foreach (SitemapConfigCategory::getItems($id_lang, $with_link) as $category)
		{
			$link = $this->link->getCategoryLink((int)$category['id_category'], $category['link_rewrite'], (int)$category['id_lang']);
			$links = array();
			foreach ($category['links'] as $l)
				$links[Language::getIsoById($l['id_lang'])] = $this->link->getCategoryLink($l['id_category'], $l['link_rewrite'], (int)$l['id_lang']);

			$sitemap->addItem($link, $category['priority'], $category['changefreq'],
				date('d-m-Y', strtotime($category['date_upd'])), array(), $links);

			$categories[$category['id_category']] = array(
				'priority' => $category['priority'],
				'changefreq' => $category['changefreq']
			);
		}

		foreach (SitemapConfigProduct::getItems($id_lang, $with_link, $with_image) as $product)
		{
			$link = $this->link->getProductLink($product['id_product'], $product['link_rewrite'], null, null, $product['id_lang']);
			$links = array();
			foreach ($product['links'] as $l)
				$links[Language::getIsoById($l['id_lang'])] = $this->link->getProductLink($l['id_product'], $l['link_rewrite'], null, null, $l['id_lang']);

			$images = array();
			foreach ($product['images'] as $image)
			{
				$thickbox_default = _PS_VERSION_ < 1.7 ? 'thickbox' : 'large';
				//$thickbox_default .= '_default';
				$images[] = array(
					'loc' => $this->link->getImageLink($product['link_rewrite'], $image['id_image'], $thickbox_default),
					'title' => $image['legend']
				);
			}

			$default = (isset($categories[$product['id_category_default']])
				? $categories[$product['id_category_default']]
				: $categories[$product['id_category']]);

			$priority = (empty($product['priority']) ? $default['priority'] : $product['priority']);
			$changefreq = (empty($product['changefreq']) ? $default['changefreq'] : $product['changefreq']);
			$sitemap->addItem($link,
				$priority,
				$changefreq,
				date('d-m-Y', strtotime($product['date_upd'])), $images, $links);
		}

		foreach (SitemapConfigCms::getItems($id_lang, $with_link) as $cms_page)
		{
			$link = $this->link->getCMSLink($cms_page['id_cms'], null, null, $cms_page['id_lang']);
			$links = array();
			foreach ($cms_page['links'] as $l)
				$links[Language::getIsoById($l['id_lang'])] = $this->link->getCMSLink($l['id_cms'], null, null, $l['id_lang']);

			$sitemap->addItem($link, $cms_page['priority'], $cms_page['changefreq'], 'Today', array(), $links);
		}

		foreach (SitemapConfigMeta::getItems($id_lang, $with_link) as $page)
		{
			$link = $this->link->getPageLink($page['page'], null, $page['id_lang']);
			$links = array();
			foreach ($page['links'] as $l)
				$links[Language::getIsoById($l['id_lang'])] = $this->link->getPageLink($l['page'], null, $l['id_lang']);

			$sitemap->addItem($link, $page['priority'], $page['changefreq'], 'Today', array(), $links);
		}

		foreach (UserLink::getAll($id_lang, $with_link) as $user_link)
			$sitemap->addItem($user_link['link'], $user_link['priority'], $user_link['changefreq'], 'Today', array(), $user_link['links']);
		
		/* feature for adding SmartBlog posts to Sitemap Pro */
		$p_urls = Db::getInstance()->ExecuteS('SELECT post.`id_smart_blog_post`, CONCAT(post.`id_smart_blog_post`,"_",postlang.link_rewrite,".html") as link '.'FROM `'._DB_PREFIX_.'smart_blog_post` post, '._DB_PREFIX_.'smart_blog_post_lang postlang '.'WHERE post.active=1 and post.id_smart_blog_post=postlang.id_smart_blog_post '.'and postlang.id_lang='.(int)$l['id_lang'].' order by id_smart_blog_post asc');
		
		foreach ($p_urls as $p_url)
		    $sitemap->addItem('/SmartBlog/' . $p_url['link'] . '', '0.7', 'weekly', 'Today', array(), $p_urls);
		/* END feature for adding SmartBlog posts to Sitemap Pro */
		
		$sitemap->createSitemapIndex(ToolsSMP::getShopDomainWithBase());
	}
}