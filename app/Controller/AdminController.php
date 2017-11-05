<?php
/**
 * webtrees: online genealogy
 * Copyright (C) 2017 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace Fisharebest\Webtrees\Controller;

use DirectoryIterator;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\File;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\Functions\Functions;
use Fisharebest\Webtrees\Html;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for the administration pages
 */
class AdminController extends BaseController {
	// This is a list of old files and directories, from earlier versions of webtrees.
	// git diff 1.7.9..master --name-status | grep ^D
	const OLD_FILES = [
		// Removed in 1.0.2
		WT_ROOT . 'language/en.mo',
		// Removed in 1.0.3
		WT_ROOT . 'themechange.php',
		// Removed in 1.1.0
		WT_ROOT . 'addremotelink.php',
		WT_ROOT . 'addsearchlink.php',
		WT_ROOT . 'client.php',
		WT_ROOT . 'dir_editor.php',
		WT_ROOT . 'editconfig_gedcom.php',
		WT_ROOT . 'editgedcoms.php',
		WT_ROOT . 'edit_merge.php',
		WT_ROOT . 'genservice.php',
		WT_ROOT . 'logs.php',
		WT_ROOT . 'manageservers.php',
		WT_ROOT . 'media.php',
		WT_ROOT . 'module_admin.php',
		//WT_ROOT.'modules', // Do not delete - users may have stored custom modules/data here
		WT_ROOT . 'opensearch.php',
		WT_ROOT . 'PEAR.php',
		WT_ROOT . 'pgv_to_wt.php',
		WT_ROOT . 'places',
		//WT_ROOT.'robots.txt', // Do not delete this - it may contain user data
		WT_ROOT . 'serviceClientTest.php',
		WT_ROOT . 'siteconfig.php',
		WT_ROOT . 'SOAP',
		WT_ROOT . 'themes/clouds/mozilla.css',
		WT_ROOT . 'themes/clouds/netscape.css',
		WT_ROOT . 'themes/colors/mozilla.css',
		WT_ROOT . 'themes/colors/netscape.css',
		WT_ROOT . 'themes/fab/mozilla.css',
		WT_ROOT . 'themes/fab/netscape.css',
		WT_ROOT . 'themes/minimal/mozilla.css',
		WT_ROOT . 'themes/minimal/netscape.css',
		WT_ROOT . 'themes/webtrees/mozilla.css',
		WT_ROOT . 'themes/webtrees/netscape.css',
		WT_ROOT . 'themes/webtrees/style_rtl.css',
		WT_ROOT . 'themes/xenea/mozilla.css',
		WT_ROOT . 'themes/xenea/netscape.css',
		WT_ROOT . 'uploadmedia.php',
		WT_ROOT . 'useradmin.php',
		WT_ROOT . 'webservice',
		WT_ROOT . 'wtinfo.php',
		// Removed in 1.1.2
		WT_ROOT . 'treenav.php',
		// Removed in 1.2.0
		WT_ROOT . 'themes/clouds/jquery',
		WT_ROOT . 'themes/colors/jquery',
		WT_ROOT . 'themes/fab/jquery',
		WT_ROOT . 'themes/minimal/jquery',
		WT_ROOT . 'themes/webtrees/jquery',
		WT_ROOT . 'themes/xenea/jquery',
		// Removed in 1.2.2
		WT_ROOT . 'themes/clouds/chrome.css',
		WT_ROOT . 'themes/clouds/opera.css',
		WT_ROOT . 'themes/clouds/print.css',
		WT_ROOT . 'themes/clouds/style_rtl.css',
		WT_ROOT . 'themes/colors/chrome.css',
		WT_ROOT . 'themes/colors/opera.css',
		WT_ROOT . 'themes/colors/print.css',
		WT_ROOT . 'themes/colors/style_rtl.css',
		WT_ROOT . 'themes/fab/chrome.css',
		WT_ROOT . 'themes/fab/opera.css',
		WT_ROOT . 'themes/minimal/chrome.css',
		WT_ROOT . 'themes/minimal/opera.css',
		WT_ROOT . 'themes/minimal/print.css',
		WT_ROOT . 'themes/minimal/style_rtl.css',
		WT_ROOT . 'themes/xenea/chrome.css',
		WT_ROOT . 'themes/xenea/opera.css',
		WT_ROOT . 'themes/xenea/print.css',
		WT_ROOT . 'themes/xenea/style_rtl.css',
		// Removed in 1.2.3
		//WT_ROOT.'modules_v2', // Do not delete - users may have stored custom modules/data here
		// Removed in 1.2.4
		WT_ROOT . 'modules_v3/gedcom_favorites/help_text.php',
		WT_ROOT . 'modules_v3/GEDFact_assistant/_MEDIA/media_3_find.php',
		WT_ROOT . 'modules_v3/GEDFact_assistant/_MEDIA/media_3_search_add.php',
		WT_ROOT . 'modules_v3/GEDFact_assistant/_MEDIA/media_5_input.js',
		WT_ROOT . 'modules_v3/GEDFact_assistant/_MEDIA/media_5_input.php',
		WT_ROOT . 'modules_v3/GEDFact_assistant/_MEDIA/media_7_parse_addLinksTbl.php',
		WT_ROOT . 'modules_v3/GEDFact_assistant/_MEDIA/media_query_1a.php',
		WT_ROOT . 'modules_v3/GEDFact_assistant/_MEDIA/media_query_2a.php',
		WT_ROOT . 'modules_v3/GEDFact_assistant/_MEDIA/media_query_3a.php',
		WT_ROOT . 'modules_v3/lightbox/css/album_page_RTL2.css',
		WT_ROOT . 'modules_v3/lightbox/css/album_page_RTL.css',
		WT_ROOT . 'modules_v3/lightbox/css/album_page_RTL_ff.css',
		WT_ROOT . 'modules_v3/lightbox/css/clearbox_music.css',
		WT_ROOT . 'modules_v3/lightbox/css/clearbox_music_RTL.css',
		WT_ROOT . 'modules_v3/user_favorites/db_schema',
		WT_ROOT . 'modules_v3/user_favorites/help_text.php',
		WT_ROOT . 'search_engine.php',
		WT_ROOT . 'themes/clouds/modules.css',
		WT_ROOT . 'themes/colors/modules.css',
		WT_ROOT . 'themes/fab/modules.css',
		WT_ROOT . 'themes/minimal/modules.css',
		WT_ROOT . 'themes/webtrees/modules.css',
		WT_ROOT . 'themes/xenea/modules.css',
		// Removed in 1.2.5
		WT_ROOT . 'modules_v3/clippings/index.php',
		WT_ROOT . 'modules_v3/googlemap/css/googlemap_style.css',
		WT_ROOT . 'modules_v3/googlemap/css/wt_v3_places_edit.css',
		WT_ROOT . 'modules_v3/googlemap/index.php',
		WT_ROOT . 'modules_v3/lightbox/index.php',
		WT_ROOT . 'modules_v3/recent_changes/help_text.php',
		WT_ROOT . 'modules_v3/todays_events/help_text.php',
		WT_ROOT . 'sidebar.php',
		// Removed in 1.2.6
		WT_ROOT . 'modules_v3/sitemap/admin_index.php',
		WT_ROOT . 'modules_v3/sitemap/help_text.php',
		WT_ROOT . 'modules_v3/tree/css/styles',
		WT_ROOT . 'modules_v3/tree/css/treebottom.gif',
		WT_ROOT . 'modules_v3/tree/css/treebottomleft.gif',
		WT_ROOT . 'modules_v3/tree/css/treebottomright.gif',
		WT_ROOT . 'modules_v3/tree/css/tree.jpg',
		WT_ROOT . 'modules_v3/tree/css/treeleft.gif',
		WT_ROOT . 'modules_v3/tree/css/treeright.gif',
		WT_ROOT . 'modules_v3/tree/css/treetop.gif',
		WT_ROOT . 'modules_v3/tree/css/treetopleft.gif',
		WT_ROOT . 'modules_v3/tree/css/treetopright.gif',
		WT_ROOT . 'modules_v3/tree/css/treeview_print.css',
		WT_ROOT . 'modules_v3/tree/help_text.php',
		WT_ROOT . 'modules_v3/tree/images/print.png',
		// Removed in 1.2.7
		WT_ROOT . 'login_register.php',
		WT_ROOT . 'modules_v3/top10_givnnames/help_text.php',
		WT_ROOT . 'modules_v3/top10_surnames/help_text.php',
		// Removed in 1.3.0
		WT_ROOT . 'admin_site_ipaddress.php',
		WT_ROOT . 'downloadgedcom.php',
		WT_ROOT . 'export_gedcom.php',
		WT_ROOT . 'gedcheck.php',
		WT_ROOT . 'images',
		WT_ROOT . 'modules_v3/googlemap/admin_editconfig.php',
		WT_ROOT . 'modules_v3/googlemap/admin_placecheck.php',
		WT_ROOT . 'modules_v3/googlemap/flags.php',
		WT_ROOT . 'modules_v3/googlemap/images/pedigree_map.gif',
		WT_ROOT . 'modules_v3/googlemap/pedigree_map.php',
		WT_ROOT . 'modules_v3/lightbox/admin_config.php',
		WT_ROOT . 'modules_v3/lightbox/album.php',
		WT_ROOT . 'modules_v3/tree/css/vline.jpg',
		// Removed in 1.3.1
		WT_ROOT . 'imageflush.php',
		WT_ROOT . 'modules_v3/googlemap/wt_v3_pedigree_map.js.php',
		WT_ROOT . 'modules_v3/lightbox/js/tip_balloon_RTL.js',
		// Removed in 1.3.2
		WT_ROOT . 'modules_v3/address_report',
		WT_ROOT . 'modules_v3/lightbox/functions/lb_horiz_sort.php',
		WT_ROOT . 'modules_v3/random_media/help_text.php',
		// Removed in 1.4.0
		WT_ROOT . 'imageview.php',
		WT_ROOT . 'media/MediaInfo.txt',
		WT_ROOT . 'media/thumbs/ThumbsInfo.txt',
		WT_ROOT . 'modules_v3/GEDFact_assistant/css/media_0_inverselink.css',
		WT_ROOT . 'modules_v3/lightbox/help_text.php',
		WT_ROOT . 'modules_v3/lightbox/images/blank.gif',
		WT_ROOT . 'modules_v3/lightbox/images/close_1.gif',
		WT_ROOT . 'modules_v3/lightbox/images/image_add.gif',
		WT_ROOT . 'modules_v3/lightbox/images/image_copy.gif',
		WT_ROOT . 'modules_v3/lightbox/images/image_delete.gif',
		WT_ROOT . 'modules_v3/lightbox/images/image_edit.gif',
		WT_ROOT . 'modules_v3/lightbox/images/image_link.gif',
		WT_ROOT . 'modules_v3/lightbox/images/images.gif',
		WT_ROOT . 'modules_v3/lightbox/images/image_view.gif',
		WT_ROOT . 'modules_v3/lightbox/images/loading.gif',
		WT_ROOT . 'modules_v3/lightbox/images/next.gif',
		WT_ROOT . 'modules_v3/lightbox/images/nextlabel.gif',
		WT_ROOT . 'modules_v3/lightbox/images/norm_2.gif',
		WT_ROOT . 'modules_v3/lightbox/images/overlay.png',
		WT_ROOT . 'modules_v3/lightbox/images/prev.gif',
		WT_ROOT . 'modules_v3/lightbox/images/prevlabel.gif',
		WT_ROOT . 'modules_v3/lightbox/images/private.gif',
		WT_ROOT . 'modules_v3/lightbox/images/slideshow.jpg',
		WT_ROOT . 'modules_v3/lightbox/images/transp80px.gif',
		WT_ROOT . 'modules_v3/lightbox/images/zoom_1.gif',
		WT_ROOT . 'modules_v3/lightbox/js',
		WT_ROOT . 'modules_v3/lightbox/music',
		WT_ROOT . 'modules_v3/lightbox/pic',
		WT_ROOT . 'themes/_administration/jquery',
		WT_ROOT . 'themes/webtrees/chrome.css',
		// Removed in 1.4.1
		WT_ROOT . 'modules_v3/lightbox/images/image_edit.png',
		WT_ROOT . 'modules_v3/lightbox/images/image_view.png',
		// Removed in 1.4.2
		WT_ROOT . 'modules_v3/lightbox/images/image_view.png',
		WT_ROOT . 'modules_v3/top10_pageviews/help_text.php',
		WT_ROOT . 'themes/_administration/jquery-ui-1.10.0',
		WT_ROOT . 'themes/clouds/jquery-ui-1.10.0',
		WT_ROOT . 'themes/colors/jquery-ui-1.10.0',
		WT_ROOT . 'themes/fab/jquery-ui-1.10.0',
		WT_ROOT . 'themes/minimal/jquery-ui-1.10.0',
		WT_ROOT . 'themes/webtrees/jquery-ui-1.10.0',
		WT_ROOT . 'themes/xenea/jquery-ui-1.10.0',
		// Removed in 1.5.0
		WT_ROOT . 'modules_v3/GEDFact_assistant/_CENS/census_note_decode.php',
		WT_ROOT . 'modules_v3/GEDFact_assistant/_CENS/census_asst_date.php',
		WT_ROOT . 'modules_v3/googlemap/wt_v3_googlemap.js.php',
		WT_ROOT . 'modules_v3/lightbox/functions/lightbox_print_media.php',
		WT_ROOT . 'modules_v3/upcoming_events/help_text.php',
		WT_ROOT . 'modules_v3/stories/help_text.php',
		WT_ROOT . 'modules_v3/user_messages/help_text.php',
		WT_ROOT . 'themes/_administration/favicon.png',
		WT_ROOT . 'themes/_administration/images',
		WT_ROOT . 'themes/_administration/msie.css',
		WT_ROOT . 'themes/_administration/style.css',
		WT_ROOT . 'themes/clouds/favicon.png',
		WT_ROOT . 'themes/clouds/images',
		WT_ROOT . 'themes/clouds/msie.css',
		WT_ROOT . 'themes/clouds/style.css',
		WT_ROOT . 'themes/colors/css',
		WT_ROOT . 'themes/colors/favicon.png',
		WT_ROOT . 'themes/colors/images',
		WT_ROOT . 'themes/colors/ipad.css',
		WT_ROOT . 'themes/colors/msie.css',
		WT_ROOT . 'themes/fab/favicon.png',
		WT_ROOT . 'themes/fab/images',
		WT_ROOT . 'themes/fab/msie.css',
		WT_ROOT . 'themes/fab/style.css',
		WT_ROOT . 'themes/minimal/favicon.png',
		WT_ROOT . 'themes/minimal/images',
		WT_ROOT . 'themes/minimal/msie.css',
		WT_ROOT . 'themes/minimal/style.css',
		WT_ROOT . 'themes/webtrees/favicon.png',
		WT_ROOT . 'themes/webtrees/images',
		WT_ROOT . 'themes/webtrees/msie.css',
		WT_ROOT . 'themes/webtrees/style.css',
		WT_ROOT . 'themes/xenea/favicon.png',
		WT_ROOT . 'themes/xenea/images',
		WT_ROOT . 'themes/xenea/msie.css',
		WT_ROOT . 'themes/xenea/style.css',
		// Removed in 1.5.1
		WT_ROOT . 'themes/_administration/css-1.5.0',
		WT_ROOT . 'themes/clouds/css-1.5.0',
		WT_ROOT . 'themes/colors/css-1.5.0',
		WT_ROOT . 'themes/fab/css-1.5.0',
		WT_ROOT . 'themes/minimal/css-1.5.0',
		WT_ROOT . 'themes/webtrees/css-1.5.0',
		WT_ROOT . 'themes/xenea/css-1.5.0',
		// Removed in 1.5.2
		WT_ROOT . 'themes/_administration/css-1.5.1',
		WT_ROOT . 'themes/clouds/css-1.5.1',
		WT_ROOT . 'themes/colors/css-1.5.1',
		WT_ROOT . 'themes/fab/css-1.5.1',
		WT_ROOT . 'themes/minimal/css-1.5.1',
		WT_ROOT . 'themes/webtrees/css-1.5.1',
		WT_ROOT . 'themes/xenea/css-1.5.1',
		// Removed in 1.5.3
		WT_ROOT . 'modules_v3/GEDFact_assistant/_CENS/census_asst_help.php',
		WT_ROOT . 'modules_v3/googlemap/admin_places.php',
		WT_ROOT . 'modules_v3/googlemap/defaultconfig.php',
		WT_ROOT . 'modules_v3/googlemap/googlemap.php',
		WT_ROOT . 'modules_v3/googlemap/placehierarchy.php',
		WT_ROOT . 'modules_v3/googlemap/places_edit.php',
		WT_ROOT . 'modules_v3/googlemap/util.js',
		WT_ROOT . 'modules_v3/googlemap/wt_v3_places_edit.js.php',
		WT_ROOT . 'modules_v3/googlemap/wt_v3_places_edit_overlays.js.php',
		WT_ROOT . 'modules_v3/googlemap/wt_v3_street_view.php',
		WT_ROOT . 'readme.html',
		WT_ROOT . 'themes/_administration/css-1.5.2',
		WT_ROOT . 'themes/clouds/css-1.5.2',
		WT_ROOT . 'themes/colors/css-1.5.2',
		WT_ROOT . 'themes/fab/css-1.5.2',
		WT_ROOT . 'themes/minimal/css-1.5.2',
		WT_ROOT . 'themes/webtrees/css-1.5.2',
		WT_ROOT . 'themes/xenea/css-1.5.2',
		// Removed in 1.6.0
		WT_ROOT . 'downloadbackup.php',
		WT_ROOT . 'modules_v3/ckeditor/ckeditor-4.3.2-custom',
		WT_ROOT . 'site-php-version.php',
		WT_ROOT . 'themes/_administration/css-1.5.3',
		WT_ROOT . 'themes/clouds/css-1.5.3',
		WT_ROOT . 'themes/colors/css-1.5.3',
		WT_ROOT . 'themes/fab/css-1.5.3',
		WT_ROOT . 'themes/minimal/css-1.5.3',
		WT_ROOT . 'themes/webtrees/css-1.5.3',
		WT_ROOT . 'themes/xenea/css-1.5.3',
		// Removed in 1.6.2
		WT_ROOT . 'themes/_administration/css-1.6.0',
		WT_ROOT . 'themes/_administration/jquery-ui-1.10.3',
		WT_ROOT . 'themes/clouds/css-1.6.0',
		WT_ROOT . 'themes/clouds/jquery-ui-1.10.3',
		WT_ROOT . 'themes/colors/css-1.6.0',
		WT_ROOT . 'themes/colors/jquery-ui-1.10.3',
		WT_ROOT . 'themes/fab/css-1.6.0',
		WT_ROOT . 'themes/fab/jquery-ui-1.10.3',
		WT_ROOT . 'themes/minimal/css-1.6.0',
		WT_ROOT . 'themes/minimal/jquery-ui-1.10.3',
		WT_ROOT . 'themes/webtrees/css-1.6.0',
		WT_ROOT . 'themes/webtrees/jquery-ui-1.10.3',
		WT_ROOT . 'themes/xenea/css-1.6.0',
		WT_ROOT . 'themes/xenea/jquery-ui-1.10.3',
		WT_ROOT . 'themes/_administration/css-1.6.0',
		WT_ROOT . 'themes/_administration/jquery-ui-1.10.3',
		// Removed in 1.7.0
		WT_ROOT . 'admin_site_other.php',
		WT_ROOT . 'js',
		WT_ROOT . 'language/en_GB.mo', // Replaced with en-GB.mo
		WT_ROOT . 'language/en_US.mo', // Replaced with en-US.mo
		WT_ROOT . 'language/pt_BR.mo', // Replaced with pt-BR.mo
		WT_ROOT . 'language/zh_CN.mo', // Replaced with zh-Hans.mo
		WT_ROOT . 'language/extra',
		WT_ROOT . 'library',
		WT_ROOT . 'modules_v3/batch_update/admin_batch_update.php',
		WT_ROOT . 'modules_v3/batch_update/plugins',
		WT_ROOT . 'modules_v3/charts/help_text.php',
		WT_ROOT . 'modules_v3/ckeditor/ckeditor-4.4.1-custom',
		WT_ROOT . 'modules_v3/clippings/clippings_ctrl.php',
		WT_ROOT . 'modules_v3/clippings/help_text.php',
		WT_ROOT . 'modules_v3/faq/help_text.php',
		WT_ROOT . 'modules_v3/gedcom_favorites/db_schema',
		WT_ROOT . 'modules_v3/gedcom_news/db_schema',
		WT_ROOT . 'modules_v3/googlemap/db_schema',
		WT_ROOT . 'modules_v3/googlemap/help_text.php',
		WT_ROOT . 'modules_v3/html/help_text.php',
		WT_ROOT . 'modules_v3/logged_in/help_text.php',
		WT_ROOT . 'modules_v3/review_changes/help_text.php',
		WT_ROOT . 'modules_v3/todo/help_text.php',
		WT_ROOT . 'modules_v3/tree/class_treeview.php',
		WT_ROOT . 'modules_v3/user_blog/db_schema',
		WT_ROOT . 'modules_v3/yahrzeit/help_text.php',
		WT_ROOT . 'save.php',
		WT_ROOT . 'themes/_administration/css-1.6.2',
		WT_ROOT . 'themes/_administration/templates',
		WT_ROOT . 'themes/_administration/header.php',
		WT_ROOT . 'themes/_administration/footer.php',
		WT_ROOT . 'themes/clouds/css-1.6.2',
		WT_ROOT . 'themes/clouds/templates',
		WT_ROOT . 'themes/clouds/header.php',
		WT_ROOT . 'themes/clouds/footer.php',
		WT_ROOT . 'themes/colors/css-1.6.2',
		WT_ROOT . 'themes/colors/templates',
		WT_ROOT . 'themes/colors/header.php',
		WT_ROOT . 'themes/colors/footer.php',
		WT_ROOT . 'themes/fab/css-1.6.2',
		WT_ROOT . 'themes/fab/templates',
		WT_ROOT . 'themes/fab/header.php',
		WT_ROOT . 'themes/fab/footer.php',
		WT_ROOT . 'themes/minimal/css-1.6.2',
		WT_ROOT . 'themes/minimal/templates',
		WT_ROOT . 'themes/minimal/header.php',
		WT_ROOT . 'themes/minimal/footer.php',
		WT_ROOT . 'themes/webtrees/css-1.6.2',
		WT_ROOT . 'themes/webtrees/templates',
		WT_ROOT . 'themes/webtrees/header.php',
		WT_ROOT . 'themes/webtrees/footer.php',
		WT_ROOT . 'themes/xenea/css-1.6.2',
		WT_ROOT . 'themes/xenea/templates',
		WT_ROOT . 'themes/xenea/header.php',
		WT_ROOT . 'themes/xenea/footer.php',
		// Removed in 1.7.2
		WT_ROOT . 'assets/js-1.7.0',
		WT_ROOT . 'packages/bootstrap-3.3.4',
		WT_ROOT . 'packages/bootstrap-datetimepicker-4.0.0',
		WT_ROOT . 'packages/ckeditor-4.4.7-custom',
		WT_ROOT . 'packages/font-awesome-4.3.0',
		WT_ROOT . 'packages/jquery-1.11.2',
		WT_ROOT . 'packages/jquery-2.1.3',
		WT_ROOT . 'packages/moment-2.10.3',
		// Removed in 1.7.3
		WT_ROOT . 'modules_v3/GEDFact_assistant/census/date.js',
		WT_ROOT . 'modules_v3/GEDFact_assistant/census/dynamicoptionlist.js',
		WT_ROOT . 'packages/jquery-cookie-1.4.1/jquery.cookie.js',
		// Removed in 1.7.4
		WT_ROOT . 'assets/js-1.7.2',
		WT_ROOT . 'themes/_administration/css-1.7.0',
		WT_ROOT . 'themes/clouds/css-1.7.0',
		WT_ROOT . 'themes/colors/css-1.7.0',
		WT_ROOT . 'themes/fab/css-1.7.0',
		WT_ROOT . 'themes/minimal/css-1.7.0',
		WT_ROOT . 'themes/webtrees/css-1.7.0',
		WT_ROOT . 'themes/xenea/css-1.7.0',
		WT_ROOT . 'packages/bootstrap-3.3.5',
		WT_ROOT . 'packages/bootstrap-datetimepicker-4.15.35',
		WT_ROOT . 'packages/jquery-1.11.3',
		WT_ROOT . 'packages/jquery-2.1.4',
		WT_ROOT . 'packages/moment-2.10.6',
		// Removed in 1.7.5
		WT_ROOT . 'themes/_administration/css-1.7.4',
		WT_ROOT . 'themes/clouds/css-1.7.4',
		WT_ROOT . 'themes/colors/css-1.7.4',
		WT_ROOT . 'themes/fab/css-1.7.4',
		WT_ROOT . 'themes/minimal/css-1.7.4',
		WT_ROOT . 'themes/webtrees/css-1.7.4',
		WT_ROOT . 'themes/xenea/css-1.7.4',
		// Removed in 1.7.7
		WT_ROOT . 'assets/js-1.7.4',
		WT_ROOT . 'modules_v3/googlemap/images/css_sprite_facts.png',
		WT_ROOT . 'modules_v3/googlemap/images/flag_shadow.png',
		WT_ROOT . 'modules_v3/googlemap/images/shadow-left-large.png',
		WT_ROOT . 'modules_v3/googlemap/images/shadow-left-small.png',
		WT_ROOT . 'modules_v3/googlemap/images/shadow-right-large.png',
		WT_ROOT . 'modules_v3/googlemap/images/shadow-right-small.png',
		WT_ROOT . 'modules_v3/googlemap/images/shadow50.png',
		WT_ROOT . 'modules_v3/googlemap/images/transparent-left-large.png',
		WT_ROOT . 'modules_v3/googlemap/images/transparent-left-small.png',
		WT_ROOT . 'modules_v3/googlemap/images/transparent-right-large.png',
		WT_ROOT . 'modules_v3/googlemap/images/transparent-right-small.png',
		// Removed in 1.7.8
		WT_ROOT . 'themes/clouds/css-1.7.5',
		WT_ROOT . 'themes/colors/css-1.7.5',
		WT_ROOT . 'themes/fab/css-1.7.5',
		WT_ROOT . 'themes/minimal/css-1.7.5',
		WT_ROOT . 'themes/webtrees/css-1.7.5',
		WT_ROOT . 'themes/xenea/css-1.7.5',
		// Removed in 2.0.0
		WT_ROOT . 'admin_module_blocks.php',
		WT_ROOT . 'admin_module_charts.php',
		WT_ROOT . 'admin_module_menus.php',
		WT_ROOT . 'admin_module_reports.php',
		WT_ROOT . 'admin_module_sidebar.php',
		WT_ROOT . 'admin_module_tabs.php',
		WT_ROOT . 'admin_modules.php',
		WT_ROOT . 'admin_site_access.php',
		WT_ROOT . 'admin_site_clean.php',
		WT_ROOT . 'admin_site_info.php',
		WT_ROOT . 'admin_site_readme.php',
		WT_ROOT . 'app/Controller/CompactController.php',
		WT_ROOT . 'app/Controller/SimpleController.php',
		WT_ROOT . 'assets/js-1.7.7',
		WT_ROOT . 'data/html_purifier_cache',
		WT_ROOT . 'packages/datatables-1.10.15',
		WT_ROOT . 'packages/font-awesome-4.4.0',
		WT_ROOT . 'packages/jquery-1.12.1',
		WT_ROOT . 'packages/jquery-2.2.1',
		WT_ROOT . 'packages/modernizr-2.8.3',
		WT_ROOT . 'packages/respond-1.4.2',
		WT_ROOT . 'themes/_administration/css-1.7.5',
		WT_ROOT . 'themes/_administration/jquery-ui-1.11.2',
		WT_ROOT . 'themes/clouds/css-1.7.8',
		WT_ROOT . 'themes/clouds/jquery-ui-1.11.2',
		WT_ROOT . 'themes/colors/css-1.7.8',
		WT_ROOT . 'themes/colors/jquery-ui-1.11.2',
		WT_ROOT . 'themes/fab/css-1.7.8',
		WT_ROOT . 'themes/fab/jquery-ui-1.11.2',
		WT_ROOT . 'themes/minimal/css-1.7.8',
		WT_ROOT . 'themes/minimal/jquery-ui-1.11.2',
		WT_ROOT . 'themes/webtrees/css-1.7.8',
		WT_ROOT . 'themes/webtrees/jquery-ui-1.11.2',
		WT_ROOT . 'themes/xenea/css-1.7.8',
		WT_ROOT . 'themes/xenea/jquery-ui-1.11.2',
	];

	/**
	 * Show the admin page for blocks.
	 *
	 * @return Response
	 */
	public function blocks(): Response {
		return $this->components('block', 'blocks', I18N::translate('Block'), I18N::translate('Blocks'));
	}

	/**
	 * Show the admin page for charts.
	 *
	 * @return Response
	 */
	public function charts(): Response {
		return $this->components('chart', 'charts', I18N::translate('Chart'), I18N::translate('Charts'));
	}

	/**
	 * Show old user files in the data folder.
	 *
	 * @return Response
	 */
	public function cleanData(): Response {
		$protected = ['.htaccess', '.gitignore', 'index.php', 'config.ini.php'];

		// If we are storing the media in the data folder (this is the default), then don’t delete it.
		foreach (Tree::getAll() as $tree) {
			$MEDIA_DIRECTORY = $tree->getPreference('MEDIA_DIRECTORY');
			list($folder) = explode('/', $MEDIA_DIRECTORY);

			if ($folder !== '..') {
				$protected[] = $folder;
			}
		}

		$entries = [];

		foreach (new DirectoryIterator(WT_DATA_DIR) as $file) {
			$entries[] = $file->getFilename();
		}
		$entries = array_diff($entries, ['.', '..']);

		return $this->viewResponse('admin/clean-data', [
			'title'     => I18N::translate('Clean-up data folder'),
			'entries'   => $entries,
			'protected' => $protected,
		]);
	}

	/**
	 * Delete old user files in the data folder.
	 *
	 * @param Request $request
	 *
	 * @return RedirectResponse
	 */
	public function cleanDataAction(Request $request): RedirectResponse {
		$to_delete = (array) $request->get('to_delete');
		$to_delete = array_filter($to_delete);

		foreach ($to_delete as $path) {
			// Show different feedback message for files and folders.
			$is_dir = is_dir(WT_DATA_DIR . $path);

			if (File::delete(WT_DATA_DIR . $path)) {
				if ($is_dir) {
					FlashMessages::addMessage(I18N::translate('The folder %s has been deleted.', Html::escape($path)), 'success');
				} else {
					FlashMessages::addMessage(I18N::translate('The file %s has been deleted.', Html::escape($path)), 'success');
				}
			} else {
				if ($is_dir) {
					FlashMessages::addMessage(I18N::translate('The folder %s could not be deleted.', Html::escape($path)), 'danger');
				} else {
					FlashMessages::addMessage(I18N::translate('The file %s could not be deleted.', Html::escape($path)), 'danger');
				}
			}
		}

		return $this->redirectResponse('admin.php', ['route' => 'admin-control-panel']);
	}

	/**
	 * The control panel shows a summary of the site and links to admin functions.
	 *
	 * @return Response
	 */
	public function controlPanel(): Response {
		return $this->viewResponse('admin/control-panel', [
			'title'           => I18N::translate('Control panel'),
			'server_warnings' => $this->serverWarnings(),
			'latest_version'  => $this->latestVersion(),
			'all_users'       => User::all(),
			'administrators'  => User::administrators(),
			'managers'        => User::managers(),
			'moderators'      => User::moderators(),
			'unapproved'      => User::unapproved(),
			'unverified'      => User::unverified(),
			'all_trees'       => Tree::getAll(),
			'changes'         => $this->totalChanges(),
			'individuals'     => $this->totalIndividuals(),
			'families'        => $this->totalFamilies(),
			'sources'         => $this->totalSources(),
			'media'           => $this->totalMediaObjects(),
			'repositories'    => $this->totalRepositories(),
			'notes'           => $this->totalNotes(),
			'files_to_delete' => $this->filesToDelete(),
			'all_modules'     => Module::getInstalledModules('disabled'),
			'deleted_modules' => $this->deletedModuleNames(),
			'config_modules'  => Module::configurableModules(),
		]);
	}

	/**
	 * Managers see a restricted version of the contol panel.
	 *
	 * @return Response
	 */
	public function controlPanelManager(): Response {
		$all_trees = array_filter(Tree::getAll(), function (Tree $tree) {
			return Auth::isManager($tree);
		});

		return $this->viewResponse('admin/control-panel-manager', [
			'title'        => I18N::translate('Control panel'),
			'all_trees'    => $all_trees,
			'changes'      => $this->totalChanges(),
			'individuals'  => $this->totalIndividuals(),
			'families'     => $this->totalFamilies(),
			'sources'      => $this->totalSources(),
			'media'        => $this->totalMediaObjects(),
			'repositories' => $this->totalRepositories(),
			'notes'        => $this->totalNotes(),
		]);
	}

	/**
	 * Show the administrator a list of modules.
	 *
	 * @return Response
	 */
	public function modules(): Response {
		$javascript = '$(".table-module-administration").dataTable({' . I18N::datatablesI18N() . '});';
		$module_status = Database::prepare("SELECT module_name, status FROM `##module`")->fetchAssoc();

		return $this->viewResponse('admin/modules', [
			'title'             => I18N::translate('Module administration'),
			'modules'           => Module::getInstalledModules('disabled'),
			'module_status'     => $module_status,
			'deleted_modules'   => $this->deletedModuleNames(),
			'core_module_names' => Module::getCoreModuleNames(),
			'javascript'        => $javascript,
		]);
	}

	/**
	 * Delete the database settings for a deleted module.
	 *
	 * @param Request $request
	 *
	 * @return RedirectResponse
	 */
	public function deleteModuleSettings(Request $request): RedirectResponse {
		$module_name = $request->get('module_name');

		Database::prepare(
			"DELETE `##block_setting`" .
			" FROM `##block_setting`" .
			" JOIN `##block` USING (block_id)" .
			" JOIN `##module` USING (module_name)" .
			" WHERE module_name=?"
		)->execute([$module_name]);
		Database::prepare(
			"DELETE `##block`" .
			" FROM `##block`" .
			" JOIN `##module` USING (module_name)" .
			" WHERE module_name=?"
		)->execute([$module_name]);
		Database::prepare("DELETE FROM `##module_setting` WHERE module_name=?")->execute([$module_name]);
		Database::prepare("DELETE FROM `##module_privacy` WHERE module_name=?")->execute([$module_name]);
		Database::prepare("DELETE FROM `##module` WHERE module_name=?")->execute([$module_name]);

		FlashMessages::addMessage(I18N::translate('The preferences for the module “%s” have been deleted.', $module_name), 'success');

		return $this->redirectResponse('admin.php', ['route' => 'admin-modules']);
	}

	/**
	 * Show the admin page for menus.
	 *
	 * @return Response
	 */
	public function menus(): Response {
		return $this->components('menu', 'menus', I18N::translate('Menu'), I18N::translate('Menus'));
	}

	/**
	 * Show the admin page for reports.
	 *
	 * @return Response
	 */
	public function reports(): Response {
		return $this->components('report', 'reports', I18N::translate('Report'), I18N::translate('Reports'));
	}

	/**
	 * Show the server information page.
	 *
	 * @return Response
	 */
	public function serverInformation(): Response {
		$mysql_variables = Database::prepare("SHOW VARIABLES")->fetchAssoc();
		$mysql_variables = array_map(function ($text) {
			return str_replace(',', ', ', $text);
		}, $mysql_variables);

		ob_start();
		phpinfo(INFO_ALL & ~INFO_CREDITS & ~INFO_LICENSE);
		$phpinfo = ob_get_clean();
		preg_match('%<body>(.*)</body>%s', $phpinfo, $matches);
		$phpinfo = $matches[1];

		return $this->viewResponse('admin/server-information', [
			'title'           => I18N::translate('Server information'),
			'phpinfo'         => $phpinfo,
			'mysql_variables' => $mysql_variables,
		]);
	}

	/**
	 * Show the admin page for sidebars.
	 *
	 * @return Response
	 */
	public function sidebars(): Response {
		return $this->components('sidebar', 'sidebars', I18N::translate('Sidebar'), I18N::translate('Sidebars'));
	}

	/**
	 * Show the admin page for tabs.
	 *
	 * @return Response
	 */
	public function tabs(): Response {
		return $this->components('tab', 'tabs', I18N::translate('Tab'), I18N::translate('Tabs'));
	}

	/**
	 * Update the access levels of the modules.
	 *
	 * @param Request $request
	 *
	 * @return RedirectResponse
	 */
	public function updateModuleAccess(Request $request): RedirectResponse {
		$component = $request->get('component');
		$modules   = Module::getAllModulesByComponent($component);

		foreach ($modules as $module) {
			foreach (Tree::getAll() as $tree) {
				$key          = 'access-' . $module->getName() . '-' . $tree->getTreeId();
				$access_level = (int) $request->get($key, $module->defaultAccessLevel());

				Database::prepare(
					"REPLACE INTO `##module_privacy` (module_name, gedcom_id, component, access_level)" .
					" VALUES (:module_name, :tree_id, :component, :access_level)"
				)->execute([
					'module_name'  => $module->getName(),
					'tree_id'      => $tree->getTreeId(),
					'component'    => $component,
					'access_level' => $access_level,
				]);
			}
		}

		return $this->redirectResponse('admin.php', ['route' => 'admin-' . $component . 's']);
	}

	/**
	 * Update the enabled/disabled status of the modules.
	 *
	 * @param Request $request
	 *
	 * @return RedirectResponse
	 */
	public function updateModuleStatus(Request $request): RedirectResponse {
		$modules       = Module::getInstalledModules('disabled');
		$module_status = Database::prepare(
			"SELECT module_name, status FROM `##module`"
		)->fetchAssoc();

		foreach ($modules as $module) {
			$new_status = (bool) $request->get('status-' . $module->getName())  ? 'enabled' : 'disabled';
			$old_status = $module_status[$module->getName()];

			if ($new_status !== $old_status) {
				Database::prepare(
					"UPDATE `##module` SET status = :status WHERE module_name = :module_name"
				)->execute([
					'status'      => $new_status,
					'module_name' => $module->getName(),
					]);

				if ($new_status === 'enabled') {
					FlashMessages::addMessage(I18N::translate('The module “%s” has been enabled.', $module->getTitle()), 'success');
				} else {
					FlashMessages::addMessage(I18N::translate('The module “%s” has been disabled.', $module->getTitle()), 'success');
				}
			}
		}

		return $this->redirectResponse('admin.php', ['route' => 'admin-modules']);
	}

	/**
	 * Create a response object from a view.
	 *
	 * @param string   $name
	 * @param string[] $data
	 *
	 * @return Response
	 */
	protected function viewResponse($name, $data): Response {
		$html = View::make('layouts/administration', [
			'content'    => View::make($name, $data),
			'javascript' => $data['javascript'] ?? '',
			'title'      => strip_tags($data['title'] ?? ''),
			'common_url' => 'themes/_common/css-2.0.0/',
			'theme_url'  => 'themes/_administration/css-2.0.0/',
		]);

		return new Response($html);
	}

	/**
	 * Show the admin page for blocks, charts, menus, reports, sidebars, tabs, etc..
	 *
	 * @param string $component
	 * @param string $route
	 * @param string $component_title
	 * @param string $page_title
	 *
	 * @return Response
	 */
	private function components($component, $route, $component_title, $title): Response {
		return $this->viewResponse('admin/module-components', [
				'component'       => $component,
				'component_title' => $component_title,
				'modules'         => Module::getAllModulesByComponent($component),
				'title'           => $title,
				'route'           => $route,
			]
		);
	}

	/**
	 * Generate a list of module names which exist in the database but not on disk.
	 *
	 * @return string[]
	 */
	private function deletedModuleNames() {
		$database_modules = Database::prepare("SELECT module_name FROM `##module`")->fetchOneColumn();
		$disk_modules     = Module::getInstalledModules('disabled');

		return array_diff($database_modules, array_keys($disk_modules));
	}

	/**
	 * A list of old files that need to be deleted.
	 *
	 * @return string[]
	 */
	private function filesToDelete() {
		$files_to_delete = [];
		foreach (self::OLD_FILES as $file) {
			// Delete the file, if we can.
			if (file_exists($file) && !File::delete($file)) {
				$files_to_delete[] = $file;
			}
		}

		return $files_to_delete;
	}

	/**
	 * Look for the latest version of webtrees.
	 *
	 * @return string
	 */
	private function latestVersion() {
		$latest_version_txt = Functions::fetchLatestVersion();
		if (preg_match('/^[0-9.]+\|[0-9.]+\|/', $latest_version_txt)) {
			list($latest_version) = explode('|', $latest_version_txt);
		} else {
			// Cannot determine the latest version.
			$latest_version = '';
		}

		return $latest_version;
	}

	/**
	 * Generate a list of potential problems with the server.
	 *
	 * @return string[]
	 */
	private function serverWarnings() {
		$php_support_url   = 'https://secure.php.net/supported-versions.php';
		$php_major_version = explode('.', PHP_VERSION)[0];
		$today             = date('Y-m-d');
		$warnings           = [];

		if ($php_major_version === 70 && $today >= '201-12-03' || $php_major_version === 71 && $today >= '2019-12-01') {
			$warnings[] = I18N::translate('Your web server is using PHP version %s, which is no longer receiving security updates. You should upgrade to a later version as soon as possible.', PHP_VERSION) .
				' <a href="' . $php_support_url . '">' . $php_support_url . '</a>';
		} elseif ($php_major_version === 70 && $today >= '2017-12-03' || $php_major_version === 71 && $today >= '2018-12-01') {
			$warnings[] = I18N::translate('Your web server is using PHP version %s, which is no longer maintained. You should upgrade to a later version.', PHP_VERSION) . ' <a href="' . $php_support_url . '">' . $php_support_url . '</a>';
		}

		return $warnings;
	}

	/**
	 * Count the number of pending changes in each tree.
	 *
	 * @return string[]
	 */
	private function totalChanges() {
		return Database::prepare(
			"SELECT SQL_CACHE g.gedcom_id, COUNT(change_id)" .
			" FROM `##gedcom` AS g" .
			" LEFT JOIN `##change` AS c ON g.gedcom_id = c.gedcom_id AND status = 'pending'" .
			" GROUP BY g.gedcom_id"
		)->fetchAssoc();
	}

	/**
	 * Count the number of families in each tree.
	 *
	 * @return string[]
	 */
	private function totalFamilies() {
		return Database::prepare(
			"SELECT SQL_CACHE gedcom_id, COUNT(f_id)" .
			" FROM `##gedcom`" .
			" LEFT JOIN `##families` ON gedcom_id = f_file" .
			" GROUP BY gedcom_id"
		)->fetchAssoc();
	}

	/**
	 * Count the number of individuals in each tree.
	 *
	 * @return string[]
	 */
	private function totalIndividuals() {
		return Database::prepare(
			"SELECT SQL_CACHE gedcom_id, COUNT(i_id)" .
			" FROM `##gedcom`" .
			" LEFT JOIN `##individuals` ON gedcom_id = i_file" .
			" GROUP BY gedcom_id"
		)->fetchAssoc();
	}

	/**
	 * Count the number of media objects in each tree.
	 *
	 * @return string[]
	 */
	private function totalMediaObjects() {
		return Database::prepare(
			"SELECT SQL_CACHE gedcom_id, COUNT(m_id)" .
			" FROM `##gedcom`" .
			" LEFT JOIN `##media` ON gedcom_id = m_file" .
			" GROUP BY gedcom_id"
		)->fetchAssoc();
	}

	/**
	 * Count the number of notes in each tree.
	 *
	 * @return string[]
	 */
	private function totalNotes() {
		return Database::prepare(
			"SELECT SQL_CACHE gedcom_id, COUNT(o_id)" .
			" FROM `##gedcom`" .
			" LEFT JOIN `##other` ON gedcom_id = o_file AND o_type = 'NOTE'" .
			" GROUP BY gedcom_id"

		)->fetchAssoc();
	}

	/**
	 * Count the number of repositorie in each tree.
	 *
	 * @return string[]
	 */
	private function totalRepositories() {
		return Database::prepare(
			"SELECT SQL_CACHE gedcom_id, COUNT(o_id)" .
			" FROM `##gedcom`" .
			" LEFT JOIN `##other` ON gedcom_id = o_file AND o_type = 'REPO'" .
			" GROUP BY gedcom_id"
		)->fetchAssoc();
	}

	/**
	 * Count the number of sources in each tree.
	 *
	 * @return string[]
	 */
	private function totalSources() {
		return Database::prepare(
			"SELECT SQL_CACHE gedcom_id, COUNT(s_id)" .
			" FROM `##gedcom`" .
			" LEFT JOIN `##sources` ON gedcom_id = s_file" .
			" GROUP BY gedcom_id"
		)->fetchAssoc();
	}
}
