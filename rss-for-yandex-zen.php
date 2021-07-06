<?php
/*
Plugin Name: RSS for Yandex Zen
Plugin URI: https://wordpress.org/plugins/rss-for-yandex-zen/
Description: Создание RSS-ленты для сервиса Яндекс.Дзен.
Version: 1.26
Author: Flector
Author URI: https://profiles.wordpress.org/flector#content-plugins
Text Domain: rss-for-yandex-zen
*/

//функция установки значений по умолчанию при активации плагина begin
function yzen_init() {
    $yzen_options = array();
    $yzen_options['yzrssname'] = 'zen';
    $yzen_options['yzcategory'] = "Общество";
    $yzen_options['yzrating'] = "Нет (не для взрослых)";
    $yzen_options['yztitle'] = get_bloginfo_rss('title');
    $yzen_options['yzlink'] = get_bloginfo_rss('url');
    $yzen_options['yzdescription'] = get_bloginfo_rss('description');
    $yzen_options['yzlanguage'] = "ru";
    $yzen_options['yznumber'] = "50";
    $yzen_options['yztype'] = "post";
    $yzen_options['yzfigcaption'] = "Использовать alt по возможности";
    $yzen_options['yzimgauthorselect'] = "Автор записи";
    $yzen_options['yzimgauthor'] = "";
    $yzen_options['yzauthor'] = "";
    $yzen_options['yzthumbnail'] = "disabled";
    $yzen_options['yzselectthumb'] = "";
    $yzen_options['yzseodesc'] = "disabled";
    $yzen_options['yzseoplugin'] = "Yoast SEO";
    $yzen_options['yzexcludetags'] = "disabled";
    $yzen_options['yzexcludetagslist'] = "<div>";
    $yzen_options['yzexcludetags2'] = "enabled";
    $yzen_options['yzexcludetagslist2'] = "<iframe>,<script>,<ins>,<style>,<object>";
    $yzen_options['yzexcludecontent'] = "disabled";
    $yzen_options['yzexcludecontentlist'] = esc_textarea("<!--more-->\n<p><\/p>\n<p>&nbsp;<\/p>");
    $yzen_options['yzqueryselect'] = "Все таксономии, кроме исключенных";
    $yzen_options['yztaxlist'] = "";
    $yzen_options['yztaxnewslist'] = "";
    $yzen_options['yzaddtaxlist'] = "";
    $yzen_options['yzexcerpt'] = "disabled";
    $yzen_options['yzexcludedefault'] = "disabled";

    add_option('yzen_options', $yzen_options);

    yzen_add_feed();
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
}
add_action('activate_rss-for-yandex-zen/rss-for-yandex-zen.php', 'yzen_init');
//функция установки значений по умолчанию при активации плагина end

//функция при деактивации плагина begin
function yzen_on_deactivation() {
	if ( ! current_user_can('activate_plugins') ) return;

    //удаляем ленту плагина при деактивации плагина и обновляем пермалинки begin
    $yzen_options = get_option('yzen_options');
    if (!isset($yzen_options['yzrssname'])) {$yzen_options['yzrssname']="zen";}
    global $wp_rewrite;
    if ( in_array( $yzen_options['yzrssname'], $wp_rewrite->feeds ) ) {
       unset($wp_rewrite->feeds[array_search($yzen_options['yzrssname'], $wp_rewrite->feeds)]);
    }
    $wp_rewrite->flush_rules();
    //удаляем ленту плагина при деактивации плагина и обновляем пермалинки end
}
register_deactivation_hook( __FILE__, 'yzen_on_deactivation' );
//функция при деактивации плагина end

//функция при удалении плагина begin
function yzen_on_uninstall() {
	if ( ! current_user_can('activate_plugins') ) return;
    delete_option('yzen_options');
}
register_uninstall_hook( __FILE__, 'yzen_on_uninstall' );
//функция при удалении плагина end

//загрузка файла локализации плагина begin
function yzen_setup(){
    load_plugin_textdomain('rss-for-yandex-zen');
}
add_action('init', 'yzen_setup');
//загрузка файла локализации плагина end

//добавление ссылки "Настройки" на странице со списком плагинов begin
function yzen_actions($links) {
	return array_merge(array('settings' => '<a href="options-general.php?page=rss-for-yandex-zen.php">' . __('Настройки', 'rss-for-yandex-zen') . '</a>'), $links);
}
add_filter('plugin_action_links_' . plugin_basename( __FILE__ ),'yzen_actions');
//добавление ссылки "Настройки" на странице со списком плагинов end

//функция загрузки скриптов и стилей плагина только в админке и только на странице настроек плагина begin
function yzen_files_admin($hook_suffix) {
	$purl = plugins_url('', __FILE__);

    if ( is_admin() && $hook_suffix == 'settings_page_rss-for-yandex-zen' ) {

    wp_register_script('yzen-lettering', $purl . '/inc/jquery.lettering.js');
    wp_register_script('yzen-textillate', $purl . '/inc/jquery.textillate.js');
	wp_register_style('yzen-animate', $purl . '/inc/animate.min.css');
    wp_register_script('yzen-script', $purl . '/inc/yzen-script.js', array(), '1.26');

	if(!wp_script_is('jquery')) {wp_enqueue_script('jquery');}
    wp_enqueue_script('yzen-lettering');
    wp_enqueue_script('yzen-textillate');
    wp_enqueue_style('yzen-animate');
    wp_enqueue_script('yzen-script');

    }
}
add_action('admin_enqueue_scripts', 'yzen_files_admin');
//функция загрузки скриптов и стилей плагина только в админке и только на странице настроек плагина end

//функция вывода страницы настроек плагина begin
function yzen_options_page() {
$purl = plugins_url('', __FILE__);

if (isset($_POST['submit'])) {

//проверка безопасности при сохранении настроек плагина begin
if ( ! wp_verify_nonce( $_POST['yzen_nonce'], plugin_basename(__FILE__) ) || ! current_user_can('edit_posts') ) {
   wp_die(__( 'Cheatin&#8217; uh?' ));
}
//проверка безопасности при сохранении настроек плагина end

    //проверяем и сохраняем введенные пользователем данные begin
    $yzen_options = get_option('yzen_options');

    if (!preg_match('/[^A-Za-z0-9]/', $_POST['yzrssname']))  {
        $yzen_options['yzrssname'] = $_POST['yzrssname'];
        update_option('yzen_options', $yzen_options);
        yzen_add_feed();
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }

    $yzen_options['yzcategory'] = sanitize_text_field($_POST['yzcategory']);
    $yzen_options['yzrating'] = sanitize_text_field($_POST['yzrating']);
    $yzen_options['yztitle'] = sanitize_text_field($_POST['yztitle']);
    $yzen_options['yzlink'] = esc_url_raw($_POST['yzlink']);
    $yzen_options['yzdescription'] = sanitize_text_field($_POST['yzdescription']);
    $yzen_options['yzlanguage'] = sanitize_text_field($_POST['yzlanguage']);

    $yznumber = sanitize_text_field($_POST['yznumber']);
    if (is_numeric($yznumber) && (int)$yznumber>=20) {
        $yzen_options['yznumber'] = sanitize_text_field($_POST['yznumber']);
    }

    $yzen_options['yztype'] = sanitize_text_field($_POST['yztype']);
    $yzen_options['yzfigcaption'] = sanitize_text_field($_POST['yzfigcaption']);
    $yzen_options['yzimgauthorselect'] = sanitize_text_field($_POST['yzimgauthorselect']);
    $yzen_options['yzimgauthor'] = sanitize_text_field($_POST['yzimgauthor']);
    $yzen_options['yzauthor'] = sanitize_text_field($_POST['yzauthor']);

    if(isset($_POST['yzthumbnail'])){$yzen_options['yzthumbnail'] = sanitize_text_field($_POST['yzthumbnail']);}else{$yzen_options['yzthumbnail'] = 'disabled';}
    $yzen_options['yzselectthumb'] = sanitize_text_field($_POST['yzselectthumb']);

    if(isset($_POST['yzseodesc'])){$yzen_options['yzseodesc'] = sanitize_text_field($_POST['yzseodesc']);}else{$yzen_options['yzseodesc'] = 'disabled';}
    $yzen_options['yzseoplugin'] = sanitize_text_field($_POST['yzseoplugin']);

    if(isset($_POST['yzexcludetags'])){$yzen_options['yzexcludetags'] = sanitize_text_field($_POST['yzexcludetags']);}else{$yzen_options['yzexcludetags'] = 'disabled';}
    $yzen_options['yzexcludetagslist'] = esc_textarea($_POST['yzexcludetagslist']);

    if(isset($_POST['yzexcludetags2'])){$yzen_options['yzexcludetags2'] = sanitize_text_field($_POST['yzexcludetags2']);}else{$yzen_options['yzexcludetags2'] = 'disabled';}
    $yzen_options['yzexcludetagslist2'] = esc_textarea($_POST['yzexcludetagslist2']);

    if(isset($_POST['yzexcludecontent'])){$yzen_options['yzexcludecontent'] = sanitize_text_field($_POST['yzexcludecontent']);}else{$yzen_options['yzexcludecontent'] = 'disabled';}
    $yzen_options['yzexcludecontentlist'] = addcslashes(esc_textarea($_POST['yzexcludecontentlist']), '/');


    $yzen_options['yzqueryselect'] = sanitize_text_field($_POST['yzqueryselect']);
    $yzen_options['yztaxlist'] = esc_textarea($_POST['yztaxlist']);
    $yzen_options['yztaxnewslist'] = esc_textarea($_POST['yztaxnewslist']);
    $yzen_options['yzaddtaxlist'] = esc_textarea($_POST['yzaddtaxlist']);
    if(isset($_POST['yzexcerpt'])){$yzen_options['yzexcerpt'] = sanitize_text_field($_POST['yzexcerpt']);}else{$yzen_options['yzexcerpt'] = 'disabled';}
    if(isset($_POST['yzexcludedefault'])){$yzen_options['yzexcludedefault'] = sanitize_text_field($_POST['yzexcludedefault']);}else{$yzen_options['yzexcludedefault'] = 'disabled';}

    update_option('yzen_options', $yzen_options);
    //проверяем и сохраняем введенные пользователем данные end
}
yzen_set_new_options();
$yzen_options = get_option('yzen_options');
?>
<?php   if (!empty($_POST) ) :
if ( ! wp_verify_nonce( $_POST['yzen_nonce'], plugin_basename(__FILE__) ) || ! current_user_can('edit_posts') ) {
   wp_die(__( 'Cheatin&#8217; uh?' ));
}
?>
<div id="message" class="updated fade"><p><strong><?php _e('Настройки сохранены.', 'rss-for-yandex-zen') ?></strong></p></div>
<?php endif; ?>

<div class="wrap">
<h2><?php _e('Настройки плагина &#171;Яндекс.Дзен&#187;', 'rss-for-yandex-zen'); ?></h2>

<div class="metabox-holder" id="poststuff">
<div class="meta-box-sortables">

<div class="postbox">
    <h3 style="border-bottom: 1px solid #E1E1E1;background: #f7f7f7;"><span class="tcode"><?php _e('Вам нравится этот плагин ?', 'rss-for-yandex-zen'); ?></span></h3>
    <div class="inside" style="display: block;margin-right: 12px;">
        <img src="<?php echo $purl . '/img/icon_coffee.png'; ?>" title="<?php _e('Купить мне чашку кофе :)', 'rss-for-yandex-zen'); ?>" style=" margin: 5px; float:left;" />
        <p><?php _e('Привет, меня зовут <strong>Flector</strong>.', 'rss-for-yandex-zen'); ?></p>
        <p><?php _e('Я потратил много времени на разработку этого плагина.', 'rss-for-yandex-zen'); ?> <br />
        <?php _e('Поэтому не откажусь от небольшого пожертвования :)', 'rss-for-yandex-zen'); ?></p>
        <a target="_blank" id="yadonate" href="https://money.yandex.ru/to/41001443750704/200"><?php _e('Подарить', 'rss-for-yandex-zen'); ?></a>
        <p><?php _e('Или вы можете заказать у меня услуги по WordPress, от мелких правок до создания полноценного сайта.', 'rss-for-yandex-zen'); ?><br />
        <?php _e('Быстро, качественно и дешево. Прайс-лист смотрите по адресу <a target="_blank" href="https://www.wpuslugi.ru/?from=yzen-plugin">https://www.wpuslugi.ru/</a>.', 'rss-for-yandex-zen'); ?></p>
        <div style="clear:both;"></div>
    </div>
</div>

<form action="" method="post">

<div class="postbox">

    <h3 style="border-bottom: 1px solid #EEE;background: #f7f7f7;"><span class="tcode"><?php _e("Настройки", 'rss-for-yandex-zen'); ?></span></h3>
    <div class="inside" style="display: block;">

        <table class="form-table">

        <?php if ( get_option('permalink_structure') ) {
            $kor = get_bloginfo("url") .'/feed/' . '<strong>' . $yzen_options['yzrssname'] . '</strong>/';
            $rssname = get_bloginfo("url") .'/feed/' . $yzen_options['yzrssname'] . '/';
            echo '<p>Ваша RSS-лента для Яндекс.Дзена доступна по адресу: <a target="new" href="'.$rssname.'">'.$rssname.'</a><br /><br />
            Новые правила добавления канала в сервис Яндекс.Дзен читайте на этой <a target="new" href="https://yandex.ru/support/zen/publishers/site-to-channel.html">странице</a>.<br />
            Цитата: <tt>Для сайта site.ru проверка и привязка возможна при наборе каналом 7000 дочитываний за последние семь дней. <br />Учитываются только публикации со средним временем дочитывания не менее 40 секунд</tt>.<br />
            Т.е. предлагается сначала создать и заполнить канал материалами, получить 7000 дочитываний, а уже после этого можно будет добавить ленту.
            </p>';
         } else {
            $kor = get_bloginfo("url") .'/?feed=' . '<strong>' . $yzen_options['yzrssname']. '</strong>';
            $rssname = get_bloginfo("url") .'/?feed=' . $yzen_options['yzrssname'] ;
            echo '<p>Ваша RSS-лента для Яндекс.Дзена доступна по адресу: <a target="new" href="'.$rssname.'">'.$rssname.'</a><br /><br />
            Новые правила добавления канала в сервис Яндекс.Дзен читайте на этой <a target="new" href="https://yandex.ru/support/zen/publishers/site-to-channel.html">странице</a>.<br />
            Цитата: <tt>Для сайта site.ru проверка и привязка возможна при наборе каналом 7000 дочитываний за последние семь дней. <br />Учитываются только публикации со средним временем дочитывания не менее 40 секунд</tt>.<br />
            Т.е. предлагается сначала создать и заполнить канал материалами, получить 7000 дочитываний, а уже после этого можно будет добавить ленту.
            </p>';
         } ?>

            <tr>
                <th><?php _e("Имя RSS-ленты:", "rss-for-yandex-zen") ?></th>
                <td>
                    <input type="text" name="yzrssname" size="40" value="<?php echo $yzen_options['yzrssname']; ?>" />
                    <br /><small><?php _e("Текущий URL RSS-ленты:", "rss-for-yandex-zen"); ?> <tt><?php echo $kor; ?></tt><br />
                    <?php _e("Только буквы и цифры, не меняйте без необходимости.", "rss-for-yandex-zen"); ?>
                    </small><div style="margin-bottom:20px;"></div>
                </td>
            </tr>
            <tr>
                <th><?php _e("Заголовок:", "rss-for-yandex-zen") ?></th>
                <td>
                    <input type="text" name="yztitle" size="40" value="<?php echo stripslashes($yzen_options['yztitle']); ?>" />
                    <br /><small><?php _e("Название издания.", "rss-for-yandex-zen"); ?> </small>
                </td>
            </tr>
            <tr>
                <th><?php _e("Ссылка:", "rss-for-yandex-zen") ?></th>
                <td>
                    <input type="text" name="yzlink" size="40" value="<?php echo stripslashes($yzen_options['yzlink']); ?>" />
                    <br /><small><?php _e("Адрес сайта издания.", "rss-for-yandex-zen"); ?> </small>
               </td>
            </tr>
            <tr>
                <th><?php _e("Описание:", "rss-for-yandex-zen") ?></th>
                <td>
                    <input type="text" name="yzdescription" size="40" value="<?php echo stripslashes($yzen_options['yzdescription']); ?>" />
                    <br /><small><?php _e("Описание издания.", "rss-for-yandex-zen"); ?> </small>
               </td>
            </tr>
            <tr>
                <th><?php _e("Язык:", "rss-for-yandex-zen") ?></th>
                <td>
                    <input type="text" name="yzlanguage" size="2" value="<?php echo stripslashes($yzen_options['yzlanguage']); ?>" />
                    <br /><small><?php _e("Язык статей издания в стандарте <a target='new' href='https://ru.wikipedia.org/wiki/%D0%9A%D0%BE%D0%B4%D1%8B_%D1%8F%D0%B7%D1%8B%D0%BA%D0%BE%D0%B2'>ISO 639-1</a> (Россия - <strong>ru</strong>, Украина - <strong>uk</strong> и т.д.)", "rss-for-yandex-zen"); ?> </small>
                    <div  style="margin-bottom:20px;"></div>
               </td>
            </tr>
           <tr>
                <th><?php _e("Количество записей:", "rss-for-yandex-zen") ?></th>
                <td>
                    <input type="text" name="yznumber" size="2" value="<?php echo stripslashes($yzen_options['yznumber']); ?>" />
                    <br /><small><?php _e("Количество записей в ленте (по требованиям Яндекса минимально необходимо <strong>20</strong> записей).", "rss-for-yandex-zen"); ?> </small>
               </td>
            </tr>
           <tr>
                <th><?php _e("Типы записей:", "rss-for-yandex-zen") ?></th>
                <td>
                    <input type="text" name="yztype" size="20" value="<?php echo stripslashes($yzen_options['yztype']); ?>" />
                    <br /><small><?php _e("Типы записей в ленте через запятую (<strong>post</strong> - записи, <strong>page</strong> - страницы и т.д.).<br />У произвольных типов записей должно быть поле <strong>post_content</strong>!", "rss-for-yandex-zen"); ?> </small>
               </td>
            </tr>
            <tr>
                <th><?php _e("Автор записей:", "rss-for-yandex-zen") ?></th>
                <td>
                    <input type="text" name="yzauthor" size="20" value="<?php echo stripslashes($yzen_options['yzauthor']); ?>" />
                    <br /><small><?php _e("Автор записей (если не заполнено, то будет использовано имя автора записи).", "rss-for-yandex-zen"); ?> </small>
               </td>
            </tr>
            <tr>
                <th><?php _e("Описания изображений:", 'rss-for-yandex-zen') ?></th>
                <td>
                     <select name="yzfigcaption" id="capalt" style="width: 250px;">
                        <option value="Использовать alt по возможности" <?php if ($yzen_options['yzfigcaption'] == 'Использовать alt по возможности') echo "selected='selected'" ?>><?php _e("Использовать alt по возможности", "rss-for-yandex-zen"); ?></option>
                        <option value="Использовать название записи" <?php if ($yzen_options['yzfigcaption'] == 'Использовать название записи') echo "selected='selected'" ?>><?php _e("Использовать название записи", "rss-for-yandex-zen"); ?></option>
                        <option value="Отключить описания" <?php if ($yzen_options['yzfigcaption'] == 'Отключить описания') echo "selected='selected'" ?>><?php _e("Отключить описания", "rss-for-yandex-zen"); ?></option>
                    </select>
                    <br /><small><?php _e("Разметка \"описания\" для изображений (<tt>&lt;figcaption>Описание&lt;/figcaption></tt>).", "rss-for-yandex-zen"); ?> <br />
                    <span id="altimg"><?php _e("В случае отсутствия у изображения alt-атрибута для описания изображения будет использовано название записи.", "rss-for-yandex-zen"); ?> </span></small>
                </td>
            </tr>
            <tr>
                <th><?php _e("Автор изображений:", "rss-for-yandex-zen") ?></th>
                <td>
                    <select name="yzimgauthorselect" id="imgselect" style="width: 250px;">
                        <option value="Автор записи" <?php if ($yzen_options['yzimgauthorselect'] == 'Автор записи') echo "selected='selected'" ?>><?php _e("Автор записи", "rss-for-yandex-zen"); ?></option>
                        <option value="Указать автора" <?php if ($yzen_options['yzimgauthorselect'] == 'Указать автора') echo "selected='selected'" ?>><?php _e("Указать автора", "rss-for-yandex-zen"); ?></option>
                        <option value="Отключить указание автора" <?php if ($yzen_options['yzimgauthorselect'] == 'Отключить указание автора') echo "selected='selected'" ?>><?php _e("Отключить указание автора", "rss-for-yandex-zen"); ?></option>
                    </select>
                    <br /><small><?php _e("Разметка \"автора\" для изображений (<tt>&lt;span class=\"copyright\">Автор&lt;/span></tt>).", "rss-for-yandex-zen"); ?> <br />

                    </small>
               </td>
            </tr>
            <tr id="ownname" style="display:none;">
                <th><?php _e("Имя автора изображений:", "rss-for-yandex-zen") ?></th>
                <td>
                    <input type="text" name="yzimgauthor" size="20" value="<?php echo stripslashes($yzen_options['yzimgauthor']); ?>" />
                    <br /><small><?php _e("Автор изображений (если не заполнено, то будет использовано имя автора записи).", "rss-for-yandex-zen"); ?> </small>
               </td>
            </tr>
            <tr>
                <th><?php _e("Тематика записей по умолчанию:", 'rss-for-yandex-zen') ?></th>
                <td>
                     <select name="yzcategory" style="width: 250px;">
                        <option value="Происшествия" <?php if ($yzen_options['yzcategory'] == 'Происшествия') echo "selected='selected'" ?>><?php _e("Происшествия", "rss-for-yandex-zen"); ?></option>
                        <option value="Политика" <?php if ($yzen_options['yzcategory'] == 'Политика') echo "selected='selected'" ?>><?php _e("Политика", "rss-for-yandex-zen"); ?></option>
                        <option value="Война" <?php if ($yzen_options['yzcategory'] == 'Война') echo "selected='selected'" ?>><?php _e("Война", "rss-for-yandex-zen"); ?></option>
                        <option value="Общество" <?php if ($yzen_options['yzcategory'] == 'Общество') echo "selected='selected'" ?>><?php _e("Общество", "rss-for-yandex-zen"); ?></option>
                        <option value="Экономика" <?php if ($yzen_options['yzcategory'] == 'Экономика') echo "selected='selected'" ?>><?php _e("Экономика", "rss-for-yandex-zen"); ?></option>
                        <option value="Спорт" <?php if ($yzen_options['yzcategory'] == 'Спорт') echo "selected='selected'" ?>><?php _e("Спорт", "rss-for-yandex-zen"); ?></option>
                        <option value="Технологии" <?php if ($yzen_options['yzcategory'] == 'Технологии') echo "selected='selected'" ?>><?php _e("Технологии", "rss-for-yandex-zen"); ?></option>
                        <option value="Наука" <?php if ($yzen_options['yzcategory'] == 'Наука') echo "selected='selected'" ?>><?php _e("Наука", "rss-for-yandex-zen"); ?></option>
                        <option value="Игры" <?php if ($yzen_options['yzcategory'] == 'Игры') echo "selected='selected'" ?>><?php _e("Игры", "rss-for-yandex-zen"); ?></option>
                        <option value="Музыка" <?php if ($yzen_options['yzcategory'] == 'Музыка') echo "selected='selected'" ?>><?php _e("Музыка", "rss-for-yandex-zen"); ?></option>
                        <option value="Литература" <?php if ($yzen_options['yzcategory'] == 'Литература') echo "selected='selected'" ?>><?php _e("Литература", "rss-for-yandex-zen"); ?></option>
                        <option value="Кино" <?php if ($yzen_options['yzcategory'] == 'Кино') echo "selected='selected'" ?>><?php _e("Кино", "rss-for-yandex-zen"); ?></option>
                        <option value="Культура" <?php if ($yzen_options['yzcategory'] == 'Культура') echo "selected='selected'" ?>><?php _e("Культура", "rss-for-yandex-zen"); ?></option>
                        <option value="Мода" <?php if ($yzen_options['yzcategory'] == 'Мода') echo "selected='selected'" ?>><?php _e("Мода", "rss-for-yandex-zen"); ?></option>
                        <option value="Знаменитости" <?php if ($yzen_options['yzcategory'] == 'Знаменитости') echo "selected='selected'" ?>><?php _e("Знаменитости", "rss-for-yandex-zen"); ?></option>
                        <option value="Психология" <?php if ($yzen_options['yzcategory'] == 'Психология') echo "selected='selected'" ?>><?php _e("Психология", "rss-for-yandex-zen"); ?></option>
                        <option value="Здоровье" <?php if ($yzen_options['yzcategory'] == 'Здоровье') echo "selected='selected'" ?>><?php _e("Здоровье", "rss-for-yandex-zen"); ?></option>
                        <option value="Авто" <?php if ($yzen_options['yzcategory'] == 'Авто') echo "selected='selected'" ?>><?php _e("Авто", "rss-for-yandex-zen"); ?></option>
                        <option value="Дом" <?php if ($yzen_options['yzcategory'] == 'Дом') echo "selected='selected'" ?>><?php _e("Дом", "rss-for-yandex-zen"); ?></option>
                        <option value="Хобби" <?php if ($yzen_options['yzcategory'] == 'Хобби') echo "selected='selected'" ?>><?php _e("Хобби", "rss-for-yandex-zen"); ?></option>
                        <option value="Еда" <?php if ($yzen_options['yzcategory'] == 'Еда') echo "selected='selected'" ?>><?php _e("Еда", "rss-for-yandex-zen"); ?></option>
                        <option value="Дизайн" <?php if ($yzen_options['yzcategory'] == 'Дизайн') echo "selected='selected'" ?>><?php _e("Дизайн", "rss-for-yandex-zen"); ?></option>
                        <option value="Фотографии" <?php if ($yzen_options['yzcategory'] == 'Фотографии') echo "selected='selected'" ?>><?php _e("Фотографии", "rss-for-yandex-zen"); ?></option>
                        <option value="Юмор" <?php if ($yzen_options['yzcategory'] == 'Юмор') echo "selected='selected'" ?>><?php _e("Юмор", "rss-for-yandex-zen"); ?></option>
                        <option value="Природа" <?php if ($yzen_options['yzcategory'] == 'Природа') echo "selected='selected'" ?>><?php _e("Природа", "rss-for-yandex-zen"); ?></option>
                        <option value="Путешествия" <?php if ($yzen_options['yzcategory'] == 'Путешествия') echo "selected='selected'" ?>><?php _e("Путешествия", "rss-for-yandex-zen"); ?></option>
                    </select>
                    <br /><small><?php _e("Тематика по умолчанию (если при публикации записи не задана конкретная тематика, то будет использована тематика по умолчанию).", "rss-for-yandex-zen"); ?> </small>
                </td>
            </tr>
            <tr>
                <th><?php _e("Контент для взрослых по умолчанию:", 'rss-for-yandex-zen') ?></th>
                <td>
                     <select name="yzrating" style="width: 250px;">
                        <option value="Да (для взрослых)" <?php if ($yzen_options['yzrating'] == 'Да (для взрослых)') echo "selected='selected'" ?>><?php _e("Да (для взрослых)", "rss-for-yandex-zen"); ?></option>
                        <option value="Нет (не для взрослых)" <?php if ($yzen_options['yzrating'] == 'Нет (не для взрослых)') echo "selected='selected'" ?>><?php _e("Нет (не для взрослых)", "rss-for-yandex-zen"); ?></option>
                    </select>
                    <br /><small><?php _e("Если при публикации записи не выбрана эта опция, то будет использовано значение по умолчанию. Учтите, что в понимании Яндекса контент не для взрослых подразумевает записи, которые можно показывать подросткам от <strong>13</strong> лет.", "rss-for-yandex-zen"); ?> </small>
                </td>
            </tr>

            <tr>
                <th></th>
                <td>
                    <input type="submit" name="submit" class="button button-primary" value="<?php _e('Сохранить настройки &raquo;', 'rss-for-yandex-zen'); ?>" />
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="postbox">
    <h3 style="border-bottom: 1px solid #EEE;background: #f7f7f7;"><span class="tcode"><?php _e('Продвинутые настройки', 'rss-for-yandex-zen'); ?></span></h3>
	  <div class="inside" style="padding-bottom:15px;display: block;">

        <table class="form-table">

        <p><?php _e("В данной секции находятся продвинутые настройки. <br />Пожалуйста, будьте внимательны в этом разделе!", "rss-for-yandex-zen"); ?> </p>


            <tr class="yzqueryselect">
                <th><?php _e("Включить в RSS:", "rss-for-yandex-zen") ?></th>
                <td>
                    <select name="yzqueryselect" id="yzqueryselect" style="width: 280px;">
                        <option value="Все таксономии, кроме исключенных" <?php if ($yzen_options['yzqueryselect'] == 'Все таксономии, кроме исключенных') echo "selected='selected'" ?>><?php _e("Все таксономии, кроме исключенных", "rss-for-yandex-zen"); ?></option>
                        <option value="Только указанные таксономии" <?php if ($yzen_options['yzqueryselect'] == 'Только указанные таксономии') echo "selected='selected'" ?>><?php _e("Только указанные таксономии", "rss-for-yandex-zen"); ?></option>
                    </select>
                    <br /><small><?php _e("Внимание! Будьте осторожны с этой настройкой!", "rss-for-yandex-zen"); ?> <br />
                    <span id="includespan"><?php _e("Обязательно установите ниже таксономии для включения в ленту - иначе лента будет пустая.", "rss-for-yandex-zen"); ?> <br /></span>
                    <span id="excludespan"><?php _e("По умолчанию в ленту попадают записи всех таксономий, кроме указанных ниже.", "rss-for-yandex-zen"); ?> <br /></span>
                    </small>
               </td>
            </tr>
            <tr class="yztaxlisttr">
                <th><?php _e("Таксономии для исключения:", 'rss-for-yandex-zen') ?></th>
                <td>
                    <textarea rows="3" cols="60" name="yztaxlist" id="yztaxlist"><?php echo stripslashes($yzen_options['yztaxlist']); ?></textarea>
                    <br /><small><?php _e("Используемый формат: <strong>taxonomy_name:id1,id2,id3</strong>", "rss-for-yandex-zen"); ?> <br />
                    <?php _e("Пример: <code>category:1,2,4</code> - записи рубрик с ID равным 1, 2 и 4 будут <strong style='color:red;'>исключены</strong> из RSS-ленты.", "rss-for-yandex-zen"); ?><br />
                    <?php _e("Каждая новая таксономия должна начинаться с новой строки.", "rss-for-yandex-zen"); ?><br />
                    <?php _e("Стандартные таксономии WordPress: рубрика: <code>category</code>, метка: <code>post_tag</code>.", "rss-for-yandex-zen"); ?>
                    </small>
                </td>
            </tr>

            <tr class="yztaxnewslisttr">
                <th>Таксономии с новостями:</th>
                <td>
                    <textarea rows="3" cols="60" name="yztaxnewslist" id="yztaxnewslist"><?php echo stripslashes($yzen_options['yztaxnewslist']); ?></textarea>
                    <br /><small>Используемый формат: <strong>1,2,3</strong><br />
                    Пример: <code>1,2,4</code> - ID категорий, которые являются новостями <br />
                </td>
            </tr>

            <tr class="yzaddtaxlisttr">
                <th><?php _e("Таксономии для добавления:", 'rss-for-yandex-zen') ?></th>
                <td>
                    <textarea rows="3" cols="60" name="yzaddtaxlist" id="yzaddtaxlist"><?php echo stripslashes($yzen_options['yzaddtaxlist']); ?></textarea>
                    <br /><small><?php _e("Используемый формат: <strong>taxonomy_name:id1,id2,id3</strong>", "rss-for-yandex-zen"); ?> <br />
                    <?php _e("Пример: <code>category:1,2,4</code> - записи рубрик с ID равным 1, 2 и 4 будут <strong style='color:red;'>добавлены</strong> в RSS-ленту.", "rss-for-yandex-zen"); ?><br />
                    <?php _e("Каждая новая таксономия должна начинаться с новой строки.", "rss-for-yandex-zen"); ?><br />
                    <?php _e("Стандартные таксономии WordPress: рубрика: <code>category</code>, метка: <code>post_tag</code>.", "rss-for-yandex-zen"); ?>
                    </small>
                </td>
            </tr>
            <tr class="yzthumbnailtr">
                <th><?php _e("Миниатюры в RSS:", 'rss-for-yandex-zen') ?></th>
                <td>
                    <label for="yzthumbnail"><input type="checkbox" value="enabled" name="yzthumbnail" id="yzthumbnail" <?php if ($yzen_options['yzthumbnail'] == 'enabled') echo "checked='checked'"; ?> /><?php _e("Добавить миниатюру к записи", "rss-for-yandex-zen"); ?></label>
                    <br /><small><?php _e("В начало записи в RSS будет добавлена миниатюра записи (изображение записи).", "rss-for-yandex-zen"); ?> <br />
                    </small>
                </td>
            </tr>
            <tr class="yzselectthumbtr" style="display:none;">
                <th><?php _e("Размер миниатюры в RSS:", 'rss-for-yandex-zen') ?></th>
                <td>
                    <select name="yzselectthumb" style="width: 250px;">
                        <?php $image_sizes = get_intermediate_image_sizes(); ?>
                        <?php foreach ($image_sizes as $size_name): ?>
                            <option value="<?php echo $size_name ?>" <?php if ($yzen_options['yzselectthumb'] == $size_name) echo "selected='selected'" ?>><?php echo $size_name ?></option>
                        <?php endforeach; ?>
                    </select>
                    <br /><small><?php _e("Выберите нужный размер миниатюры (в списке находятся все зарегистрированные на сайте размеры миниатюр). ", "rss-for-yandex-zen"); ?> </small>
                </td>
            </tr>
            <tr class="yzseodesctr">
                <th><?php _e("Описания записей:", 'rss-for-yandex-zen') ?></th>
                <td>
                    <label for="yzseodesc"><input type="checkbox" value="enabled" name="yzseodesc" id="yzseodesc" <?php if ($yzen_options['yzseodesc'] == 'enabled') echo "checked='checked'"; ?> /><?php _e("Использовать данные из SEO-плагинов", "rss-for-yandex-zen"); ?></label>
                    <br /><small><?php _e("В качестве описания записи (rss-тег <tt>&lt;description&gt;</tt>) будет использовано описание записи из выбранного SEO-плагина.", "rss-for-yandex-zen"); ?> </small>
                </td>
            </tr>
            <tr class="yzseoplugintr" style="display:none;">
                <th><?php _e("SEO-плагин:", 'rss-for-yandex-zen') ?></th>
                <td>
                    <select name="yzseoplugin" style="width: 250px;">
                        <option value="Yoast SEO" <?php if ($yzen_options['yzseoplugin'] == 'Yoast SEO') echo "selected='selected'" ?>><?php _e("Yoast SEO", "rss-for-yandex-zen"); ?></option>
                        <option value="All in One SEO Pack" <?php if ($yzen_options['yzseoplugin'] == 'All in One SEO Pack') echo "selected='selected'" ?>><?php _e("All in One SEO Pack", "rss-for-yandex-zen"); ?></option>
                    </select>
                    <br /><small><?php _e("Выберите используемый вами SEO-плагин. <br /> Если описание записи в SEO-плагине не установлено, то будет использовано стандартное описание записи (автогенерированное из первых 55 слов записи).", "rss-for-yandex-zen"); ?> </small>
                </td>
            </tr>
            <tr>
                <th><?php _e("Отрывок записей:", 'rss-for-yandex-zen') ?></th>
                <td>
                    <label for="yzexcerpt"><input type="checkbox" value="enabled" name="yzexcerpt" id="yzexcerpt" <?php if ($yzen_options['yzexcerpt'] == 'enabled') echo "checked='checked'"; ?> /><?php _e("Добавить в начало записей \"отрывок\"", "rss-for-yandex-zen"); ?></label>
                    <br /><small><?php _e("Используйте эту опцию только в случае необходимости.", "rss-for-yandex-zen"); ?> <br />
                    <?php _e("Например, когда \"отрывок\" (цитата) записи содержит контент, которого нет в самой записи.", "rss-for-yandex-zen"); ?> <br />
                    </small>
                </td>
            </tr>
            <tr class="yzexcludetagstr">
                <th><?php _e("Фильтр тегов (без контента):", 'rss-for-yandex-zen') ?></th>
                <td>
                    <label for="yzexcludetags"><input type="checkbox" value="enabled" name="yzexcludetags" id="yzexcludetags" <?php if ($yzen_options['yzexcludetags'] == 'enabled') echo "checked='checked'"; ?> /><?php _e("Удалить указанные html-теги", "rss-for-yandex-zen"); ?></label>
                    <br /><small><?php _e("Из контента записей будут удалены все указанные html-теги (<strong>сам контент этих тегов останется</strong>).", "rss-for-yandex-zen"); ?> </small>
                </td>
            </tr>
            <tr class="yzexcludetagslisttr">
                <th><?php _e("Теги для удаления:", 'rss-for-yandex-zen') ?></th>
                <td>
                    <textarea rows="3" cols="60" name="yzexcludetagslist" id="yzexcludetagslist"><?php echo stripslashes($yzen_options['yzexcludetagslist']); ?></textarea>
                    <br /><small><?php _e("Список удаляемых html-тегов через запятую.", "rss-for-yandex-zen"); ?> <br />
                    <?php _e("Указывать классы, идентификаторы и прочее не требуется.", "rss-for-yandex-zen"); ?> <br />
                    <?php _e("Самозакрывающиеся теги вроде <tt>&lt;img src=\"...\" /></tt> и <tt>&lt;br /></tt> удалить нельзя.", "rss-for-yandex-zen"); ?><br />
                    </small>
                </td>
            </tr>
            <tr class="yzexcludetags2tr">
                <th><?php _e("Фильтр тегов (с контентом):", 'rss-for-yandex-zen') ?></th>
                <td>
                    <label for="yzexcludetags2"><input type="checkbox" value="enabled" name="yzexcludetags2" id="yzexcludetags2" <?php if ($yzen_options['yzexcludetags2'] == 'enabled') echo "checked='checked'"; ?> /><?php _e("Удалить указанные html-теги", "rss-for-yandex-zen"); ?></label>
                    <br /><small><?php _e("Из контента записей будут удалены все указанные html-теги (<strong>включая сам контент этих тегов</strong>).", "rss-for-yandex-zen"); ?> </small>
                </td>
            </tr>
            <tr class="yzexcludetagslist2tr">
                <th><?php _e("Теги для удаления:", 'rss-for-yandex-zen') ?></th>
                <td>
                    <textarea rows="3" cols="60" name="yzexcludetagslist2" id="yzexcludetagslist2"><?php echo stripslashes($yzen_options['yzexcludetagslist2']); ?></textarea>
                    <br /><small><?php _e("Список удаляемых html-тегов через запятую.", "rss-for-yandex-zen"); ?> <br />
                    <?php _e("Указывать классы, идентификаторы и прочее не требуется.", "rss-for-yandex-zen"); ?> <br />
                    <?php _e("По умолчанию в список включены все теги, о которых точно известно, что они не нравятся тех. поддержке Яндекс.Дзена.", "rss-for-yandex-zen"); ?> <br />
                    <?php _e("Самозакрывающиеся теги вроде <tt>&lt;img src=\"...\" /></tt> и <tt>&lt;br /></tt> удалить нельзя.", "rss-for-yandex-zen"); ?><br />
                    </small>
                </td>
            </tr>
            <tr class="yzexcludecontenttr">
                <th><?php _e("Контент для удаления:", 'rss-for-yandex-zen') ?></th>
                <td>
                    <label for="yzexcludecontent"><input type="checkbox" value="enabled" name="yzexcludecontent" id="yzexcludecontent" <?php if ($yzen_options['yzexcludecontent'] == 'enabled') echo "checked='checked'"; ?> /><?php _e("Удалить указанный контент из RSS", "rss-for-yandex-zen"); ?></label>
                    <br /><small><?php _e("Точные вхождения указанного контента будут удалены из записей в RSS-ленте.", "rss-for-yandex-zen"); ?> </small>
                </td>
            </tr>
            <tr class="yzexcludecontentlisttr">
                <th><?php _e("Список удаляемого контента:", 'rss-for-yandex-zen') ?></th>
                <td>
                    <textarea rows="5" cols="60" name="yzexcludecontentlist" id="yzexcludecontentlist"><?php echo stripcslashes($yzen_options['yzexcludecontentlist']); ?></textarea>
                    <br /><small><?php _e("Каждый новый шаблон для удаления должен начинаться с новой строки.", "rss-for-yandex-zen"); ?> <br />
                    </small>
                </td>
            </tr>
            <tr>
                <th><?php _e("Исключать по умолчанию:", 'rss-for-yandex-zen') ?></th>
                <td>
                    <label for="yzexcludedefault"><input type="checkbox" value="enabled" name="yzexcludedefault" id="yzexcludedefault" <?php if ($yzen_options['yzexcludedefault'] == 'enabled') echo "checked='checked'"; ?> /><?php _e("По умолчанию исключать записи из ленты", "rss-for-yandex-zen"); ?></label>
                    <br /><small><?php _e("Включение этой опции установит галку на \"Исключить эту запись из RSS\" по умолчанию при публикации новых записей.", "rss-for-yandex-zen"); ?><br />
                    <?php _e("Используется <tt>action</tt> на <tt>save_post</tt> (сработает в случае автонаполняемого сайта).", "rss-for-yandex-zen"); ?>
                    </small>
                </td>
            </tr>

            <tr>
                <th></th>
                <td>
                    <input type="submit" name="submit" class="button button-primary" value="<?php _e('Сохранить настройки &raquo;', 'rss-for-yandex-zen'); ?>" />
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="postbox">
    <h3 style="border-bottom: 1px solid #EEE;background: #f7f7f7;"><span class="tcode"><?php _e('О плагине', 'rss-for-yandex-zen'); ?></span></h3>
	  <div class="inside" style="padding-bottom:15px;display: block;">


      <p><?php _e('Если вам нравится мой плагин, то, пожалуйста, поставьте ему <a target="_blank" href="https://wordpress.org/support/plugin/rss-for-yandex-zen/reviews/#new-post"><strong>5 звезд</strong></a> в репозитории.', 'rss-for-yandex-zen'); ?></p>
      <p style="margin-top:20px;margin-bottom:10px;"><?php _e('Возможно, что вам также будут интересны другие мои плагины:', 'rss-for-yandex-zen'); ?></p>

      <div class="about">
        <ul>
            <li><a target="_blank" href="https://ru.wordpress.org/plugins/rss-for-yandex-turbo/">RSS for Yandex Turbo</a> - <?php _e('создание RSS-ленты для сервиса Яндекс.Турбо.', 'rss-for-yandex-zen'); ?></li>
            <li><a target="_blank" href="https://ru.wordpress.org/plugins/bbspoiler/">BBSpoiler</a> - <?php _e('плагин позволит вам спрятать текст под тегами [spoiler]текст[/spoiler].', 'rss-for-yandex-zen'); ?></li>
            <li><a target="_blank" href="https://ru.wordpress.org/plugins/easy-textillate/">Easy Textillate</a> - <?php _e('плагин очень красиво анимирует текст (шорткодами в записях и виджетах или PHP-кодом в файлах темы).', 'rss-for-yandex-zen'); ?> </li>
            <li><a target="_blank" href="https://ru.wordpress.org/plugins/cool-image-share/">Cool Image Share</a> - <?php _e('плагин добавляет иконки социальных сетей на каждое изображение в ваших записях.', 'rss-for-yandex-zen'); ?> </li>
            <li><a target="_blank" href="https://ru.wordpress.org/plugins/today-yesterday-dates/">Today-Yesterday Dates</a> - <?php _e('относительные даты для записей за сегодня и вчера.', 'rss-for-yandex-zen'); ?> </li>
            <li><a target="_blank" href="https://ru.wordpress.org/plugins/truncate-comments/">Truncate Comments</a> - <?php _e('плагин скрывает длинные комментарии js-скриптом (в стиле Яндекса или Амазона).', 'rss-for-yandex-zen'); ?> </li>
            <li><a target="_blank" href="https://ru.wordpress.org/plugins/easy-yandex-share/">Easy Yandex Share</a> - <?php _e('продвинутый вывод блока &#8220;Яндекс.Поделиться&#8221;.', 'rss-for-yandex-zen'); ?></li>
            <li><a target="_blank" href="https://wordpress.org/plugins/hide-my-dates/">Hide My Dates</a> - <?php _e('this plugin hides post and comment publishing dates from Google.', 'rss-for-yandex-zen'); ?></li>
            <li style="margin: 3px 0px 3px 35px;"><a target="_blank" href="https://ru.wordpress.org/plugins/html5-cumulus/">HTML5 Cumulus</a> <span class="new">new</span> - <?php _e('современная (HTML5) версия классического плагина &#8220;WP-Cumulus&#8221;.', 'rss-for-yandex-zen'); ?></li>

            </ul>
      </div>


    </div>
</div>
<?php wp_nonce_field( plugin_basename(__FILE__), 'yzen_nonce'); ?>
</form>
</div>
</div>
<?php
}
//функция вывода страницы настроек плагина end

//функция добавления ссылки на страницу настроек плагина в раздел "Настройки" begin
function yzen_menu() {
	add_options_page('Яндекс.Дзен', 'Яндекс.Дзен', 'manage_options', 'rss-for-yandex-zen.php', 'yzen_options_page');
}
add_action('admin_menu', 'yzen_menu');
//функция добавления ссылки на страницу настроек плагина в раздел "Настройки" end

//подключение стилей на странице настроек плагина begin
function yzen_admin_print_scripts() {
    $post_permalink = $_SERVER["REQUEST_URI"];
    if(strpos($post_permalink, 'rss-for-yandex-zen.php') == true) : ?>
        <style>
        tt {padding: 1px 5px 1px;margin: 0 1px;background: #eaeaea;background: rgba(0,0,0,.07);font-size: 13px;font-family: Consolas,Monaco,monospace;unicode-bidi: embed;}
        #yadonate {
  color: #000;
  cursor: pointer;
  text-decoration: none;
  background-color:#ffdb4d;
  padding: 3px 26px 4px 25px;
  font-size: 15px;
  border-radius: 3px;
  border: 1px solid rgba(0,0,0,.1);
  transition: background-color .1s ease-out 0s;
}
#yadonate:hover {
  background-color:#fc0;
}
#yadonate:focus,#yadonate:active {
  outline:none;
  box-shadow: none;
}
.about li {
  list-style-type: square;
  margin: 5px 0px 3px 35px;
}
.new {
  color: #fff;
  background-color: #008ec2;
  border-radius: 6px;
  display: inline-block;
  padding-left: 4px;
  padding-right: 4px;
  text-align: center;
  font-size: 10px;
  vertical-align: super;
}
        </style>
    <?php endif; ?>
<?php }
add_action('admin_head', 'yzen_admin_print_scripts');
//подключение стилей на странице настроек плагина end

//создаем метабокс begin
function yzen_meta_box(){
    $yzen_options = get_option('yzen_options');
    $yztype = $yzen_options['yztype'];
    $yztype = explode(",", $yztype);
    add_meta_box('yzen_meta_box', 'Яндекс.Дзен', 'yzen_callback', $yztype, 'normal' , 'high');
}
add_action( 'add_meta_boxes', 'yzen_meta_box' );
//создаем метабокс end

//сохраняем метабокс begin
function yzen_save_metabox($post_id){
    global $post;

    if ( ! isset( $_POST['yzen_meta_nonce'] ) )
        return $post_id;

    if ( ! wp_verify_nonce($_POST['yzen_meta_nonce'], plugin_basename(__FILE__) ) )
		return $post_id;

	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
		return $post_id;

    if(isset($_POST["yzcategory"])){
        $yzcategory = sanitize_text_field($_POST['yzcategory']);
        update_post_meta($post->ID, 'yzcategory_meta_value', $yzcategory);
    }
    if(isset($_POST["yzrating"])){
        $yzrating = 'Да (для взрослых)';
        update_post_meta($post->ID, 'yzrating_meta_value', $yzrating);
    } else {
        $yzrating = 'Нет (не для взрослых)';
        update_post_meta($post->ID, 'yzrating_meta_value', $yzrating);
    }
    if(isset($_POST["yzrssenabled"])){
        $yzrssenabled = 'yes';
        update_post_meta($post->ID, 'yzrssenabled_meta_value', $yzrssenabled);
    } else {
        $yzrssenabled = 'no';
        update_post_meta($post->ID, 'yzrssenabled_meta_value', $yzrssenabled);
    }

}
add_action('save_post', 'yzen_save_metabox');
//сохраняем метабокс end

//выводим метабокс begin
function yzen_callback(){
    global $post;
	wp_nonce_field( plugin_basename(__FILE__), 'yzen_meta_nonce' );

    $yzen_options = get_option('yzen_options');

    $yzcategory = get_post_meta($post->ID, 'yzcategory_meta_value', true);
    if (!$yzcategory) {$yzcategory = $yzen_options['yzcategory'];}

    $yzrating = get_post_meta($post->ID, 'yzrating_meta_value', true);
    if (!$yzrating) {$yzrating = $yzen_options['yzrating'];}

    $yzrssenabled = get_post_meta($post->ID, 'yzrssenabled_meta_value', true);
    if (!$yzrssenabled) {$yzrssenabled = "no";}
    ?>

     <p><strong><?php _e("Тематика:", "rss-for-yandex-zen"); ?></strong>
     <select name="yzcategory">
        <option value="Происшествия" <?php if ($yzcategory == 'Происшествия') echo "selected='selected'" ?>><?php _e("Происшествия", "rss-for-yandex-zen"); ?></option>
        <option value="Политика" <?php if ($yzcategory == 'Политика') echo "selected='selected'" ?>><?php _e("Политика", "rss-for-yandex-zen"); ?></option>
        <option value="Война" <?php if ($yzcategory == 'Война') echo "selected='selected'" ?>><?php _e("Война", "rss-for-yandex-zen"); ?></option>
        <option value="Общество" <?php if ($yzcategory == 'Общество') echo "selected='selected'" ?>><?php _e("Общество", "rss-for-yandex-zen"); ?></option>
        <option value="Экономика" <?php if ($yzcategory == 'Экономика') echo "selected='selected'" ?>><?php _e("Экономика", "rss-for-yandex-zen"); ?></option>
        <option value="Спорт" <?php if ($yzcategory == 'Спорт') echo "selected='selected'" ?>><?php _e("Спорт", "rss-for-yandex-zen"); ?></option>
        <option value="Технологии" <?php if ($yzcategory == 'Технологии') echo "selected='selected'" ?>><?php _e("Технологии", "rss-for-yandex-zen"); ?></option>
        <option value="Наука" <?php if ($yzcategory == 'Наука') echo "selected='selected'" ?>><?php _e("Наука", "rss-for-yandex-zen"); ?></option>
        <option value="Игры" <?php if ($yzcategory == 'Игры') echo "selected='selected'" ?>><?php _e("Игры", "rss-for-yandex-zen"); ?></option>
        <option value="Музыка" <?php if ($yzcategory == 'Музыка') echo "selected='selected'" ?>><?php _e("Музыка", "rss-for-yandex-zen"); ?></option>
        <option value="Литература" <?php if ($yzcategory == 'Литература') echo "selected='selected'" ?>><?php _e("Литература", "rss-for-yandex-zen"); ?></option>
        <option value="Кино" <?php if ($yzcategory == 'Кино') echo "selected='selected'" ?>><?php _e("Кино", "rss-for-yandex-zen"); ?></option>
        <option value="Культура" <?php if ($yzcategory == 'Культура') echo "selected='selected'" ?>><?php _e("Культура", "rss-for-yandex-zen"); ?></option>
        <option value="Мода" <?php if ($yzcategory == 'Мода') echo "selected='selected'" ?>><?php _e("Мода", "rss-for-yandex-zen"); ?></option>
        <option value="Знаменитости" <?php if ($yzcategory == 'Знаменитости') echo "selected='selected'" ?>><?php _e("Знаменитости", "rss-for-yandex-zen"); ?></option>
        <option value="Психология" <?php if ($yzcategory == 'Психология') echo "selected='selected'" ?>><?php _e("Психология", "rss-for-yandex-zen"); ?></option>
        <option value="Здоровье" <?php if ($yzcategory == 'Здоровье') echo "selected='selected'" ?>><?php _e("Здоровье", "rss-for-yandex-zen"); ?></option>
        <option value="Авто" <?php if ($yzcategory == 'Авто') echo "selected='selected'" ?>><?php _e("Авто", "rss-for-yandex-zen"); ?></option>
        <option value="Дом" <?php if ($yzcategory == 'Дом') echo "selected='selected'" ?>><?php _e("Дом", "rss-for-yandex-zen"); ?></option>
        <option value="Хобби" <?php if ($yzcategory == 'Хобби') echo "selected='selected'" ?>><?php _e("Хобби", "rss-for-yandex-zen"); ?></option>
        <option value="Еда" <?php if ($yzcategory == 'Еда') echo "selected='selected'" ?>><?php _e("Еда", "rss-for-yandex-zen"); ?></option>
        <option value="Дизайн" <?php if ($yzcategory == 'Дизайн') echo "selected='selected'" ?>><?php _e("Дизайн", "rss-for-yandex-zen"); ?></option>
        <option value="Фотографии" <?php if ($yzcategory == 'Фотографии') echo "selected='selected'" ?>><?php _e("Фотографии", "rss-for-yandex-zen"); ?></option>
        <option value="Юмор" <?php if ($yzcategory == 'Юмор') echo "selected='selected'" ?>><?php _e("Юмор", "rss-for-yandex-zen"); ?></option>
        <option value="Природа" <?php if ($yzcategory == 'Природа') echo "selected='selected'" ?>><?php _e("Природа", "rss-for-yandex-zen"); ?></option>
        <option value="Путешествия" <?php if ($yzcategory == 'Путешествия') echo "selected='selected'" ?>><?php _e("Путешествия", "rss-for-yandex-zen"); ?></option>
    </select>
    </p>

    <p style="margin:5px!important;">
    <label for="yzrating"><input type="checkbox" value="enabled" name="yzrating" id="yzrating" <?php if ($yzrating == 'Да (для взрослых)') echo "checked='checked'"; ?> /><?php _e("Запись с контентом для взрослых", "rss-for-yandex-zen"); ?></label>
<br />
    <label for="yzrssenabled"><input type="checkbox" value="enabled" name="yzrssenabled" id="yzrssenabled" <?php if ($yzrssenabled == 'yes') echo "checked='checked'"; ?> /><?php _e("Исключить эту запись из RSS", "rss-for-yandex-zen"); ?></label>
    </p>

<?php }
//выводим метабокс end

//добавляем новую rss-ленту begin
function yzen_add_feed(){
    $yzen_options = get_option('yzen_options');
    if (!isset($yzen_options['yzrssname'])) {$yzen_options['yzrssname']="zen";update_option('yzen_options', $yzen_options);}
    add_feed($yzen_options['yzrssname'], 'yzen_feed_template');
}
add_action('init', 'yzen_add_feed');
//добавляем новую rss-ленту end

//шаблон для RSS-ленты Яндекс.Дзен begin
function yzen_feed_template(){
yzen_set_new_options();
$yzen_options = get_option('yzen_options');

$yztitle = $yzen_options['yztitle'];
$yzlink = $yzen_options['yzlink'];
$yzdescription = $yzen_options['yzdescription'];
$yzlanguage = $yzen_options['yzlanguage'];
$yznumber = $yzen_options['yznumber'];
$yztype = $yzen_options['yztype'];
$yztype = explode(",", $yztype);
$yzfigcaption = $yzen_options['yzfigcaption'];
$yzimgauthorselect = $yzen_options['yzimgauthorselect'];
$yzimgauthor = $yzen_options['yzimgauthor'];
$yzauthor = $yzen_options['yzauthor'];
$yzthumbnail = $yzen_options['yzthumbnail'];
$yzselectthumb = $yzen_options['yzselectthumb'];
$yzseodesc = $yzen_options['yzseodesc'];
$yzseoplugin = $yzen_options['yzseoplugin'];
$yzexcludetags = $yzen_options['yzexcludetags'];
$yzexcludetagslist = html_entity_decode($yzen_options['yzexcludetagslist']);
$yzexcludetags2 = $yzen_options['yzexcludetags2'];
$yzexcludetagslist2 = html_entity_decode($yzen_options['yzexcludetagslist2']);
$yzexcludecontent = $yzen_options['yzexcludecontent'];
$yzexcludecontentlist = html_entity_decode($yzen_options['yzexcludecontentlist']);
$tax_query = array();

$yzqueryselect = $yzen_options['yzqueryselect'];
$yztaxlist = $yzen_options['yztaxlist'];
$yztaxnewslist = $yzen_options['yztaxnewslist'];
$arr_yztaxnewslist = (!empty($yzen_options['yztaxnewslist'])) ? explode(",", $yztaxnewslist) : [];

$yzaddtaxlist = $yzen_options['yzaddtaxlist'];

if ($yzqueryselect=='Все таксономии, кроме исключенных' && $yztaxlist) {
    $textAr = explode("\n", trim($yztaxlist));
    $textAr = array_filter($textAr, 'trim');
    $tax_query = array( 'relation' => 'AND' );
    foreach ($textAr as $line) {
        $tax = explode(":", $line);
        $taxterm = explode(",", $tax[1]);
        $tax_query[] = array(
            'taxonomy' => $tax[0],
            'field'    => 'id',
            'terms'    => $taxterm,
            'operator' => 'NOT IN',
        );
    }
}
if (!$yzaddtaxlist) {$yzaddtaxlist = 'category:10000000';}
if ($yzqueryselect=='Только указанные таксономии' && $yzaddtaxlist) {
    $textAr = explode("\n", trim($yzaddtaxlist));
    $textAr = array_filter($textAr, 'trim');
    $tax_query = array( 'relation' => 'OR' );
    foreach ($textAr as $line) {
        $tax = explode(":", $line);
        $taxterm = explode(",", $tax[1]);
        $tax_query[] = array(
            'taxonomy' => $tax[0],
            'field'    => 'id',
            'terms'    => $taxterm,
            'operator' => 'IN',
        );
    }
}

$args = array('ignore_sticky_posts' => 1, 'post_type' => $yztype, 'post_status' => 'publish', 'posts_per_page' => $yznumber,'tax_query' => $tax_query,
'meta_query' => array('relation' => 'OR', array('key' => 'yzrssenabled_meta_value', 'compare' => 'NOT EXISTS',),
array('key' => 'yzrssenabled_meta_value', 'value' => 'yes', 'compare' => '!=',),));

$args_alt = apply_filters( 'yzen_query_args', $args, 8 );
if (isset($args_alt) && is_array($args_alt)) $args = $args_alt;
$query = new WP_Query( $args );

header('Content-Type: ' . feed_content_type('rss2') . '; charset=' . get_option('blog_charset'), true);
echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'.PHP_EOL;
?>
<rss version="2.0"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:media="http://search.yahoo.com/mrss/"
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:georss="http://www.georss.org/georss">
<channel>
    <title><?php echo $yztitle; ?></title>
    <link><?php echo $yzlink; ?></link>
    <description><?php echo $yzdescription; ?></description>
    <language><?php echo $yzlanguage; ?></language>
    <generator>RSS for Yandex Zen v1.26 (https://wordpress.org/plugins/rss-for-yandex-zen/)</generator>
    <?php while($query->have_posts()) : $query->the_post(); ?>
    <item>
        <title><?php the_title_rss(); ?></title>
        <link><?php the_permalink_rss(); ?></link>
        <guid><?php echo md5( get_the_guid() ); ?></guid>
        <?php $gmt_offset = get_option('gmt_offset');
              $gmt_offset_abs = floor(abs($gmt_offset));
              $gmt_offset_str = ($gmt_offset_abs > 9) ? $gmt_offset_abs.'00' : ('0'.$gmt_offset_abs.'00');
              $gmt_offset_str = $gmt_offset >= 0 ? '+' . $gmt_offset_str : '-' . $gmt_offset_str; ?>
        <pubDate><?php echo mysql2date('D, d M Y H:i:s '.$gmt_offset_str, get_date_from_gmt(get_post_time('Y-m-d H:i:s', true)), false); ?></pubDate>
        <?php $yzrating = get_post_meta(get_the_ID(), 'yzrating_meta_value', true); ?>
        <?php if ($yzrating == 'Да (для взрослых)') {
            echo '<media:rating scheme="urn:simple">adult</media:rating>'.PHP_EOL;
        } else {
            echo '<media:rating scheme="urn:simple">nonadult</media:rating>'.PHP_EOL;
        } ?>
        <?php if ($yzauthor) {
            echo '<author>'.$yzauthor.'</author>'.PHP_EOL;
        } else {
            echo '<author>'.get_the_author().'</author>'.PHP_EOL;
        } ?>
        <?php if($yzimgauthorselect == 'Указать автора' && !$yzimgauthor){$yzimgauthor = get_the_author();} ?>
        <?php if($yzimgauthorselect == 'Автор записи'){$yzimgauthor = get_the_author();} ?>
        <?php $yzcategory = get_post_meta(get_the_ID(), 'yzcategory_meta_value', true); ?>
        <?php if ($yzcategory) { echo '<category>'.$yzcategory.'</category>'.PHP_EOL; }
        else {echo '<category>'.$yzen_options['yzcategory'].'</category>'.PHP_EOL;} ?>
        <?php
        $post_cat = get_the_category();
        $arr_post_cat_id = [];
        foreach ($post_cat as $cat) {
        	$arr_post_cat_id[] = $cat->term_id;
        }
        $result_yztaxnewslist = array_intersect($arr_post_cat_id, $arr_yztaxnewslist );
        ?>
        <?php if(count($result_yztaxnewslist) == 0): ?><category>evergreen</category><?php endif; ?>
        <?php
        if ($yzthumbnail=="enabled" && has_post_thumbnail( get_the_ID() )) {
            echo '<enclosure url="' . strtok(get_the_post_thumbnail_url(get_the_ID(),$yzselectthumb), '?') . '" type="'.yzen_mime_type(strtok(get_the_post_thumbnail_url(get_the_ID(),$yzselectthumb), '?')).'"/>'.PHP_EOL;
        }
        $html = yzen_the_content_feed();

        if ($yzexcludetags != 'disabled' && $yzexcludetagslist) {
            $html = yzen_strip_tags_without_content($html, $yzexcludetagslist);
        }
        if ($yzexcludetags2 != 'disabled' && $yzexcludetagslist2) {
            $html = yzen_strip_tags_with_content($html, $yzexcludetagslist2, true);
        }
        $html = wpautop($html);

        $dom = new domDocument ('1.0','UTF-8');
        @$dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $html);
        $dom->preserveWhiteSpace = false;
        $urltoimages = $dom->getElementsByTagName('a');
        $final  = array();
        foreach ($urltoimages as $urltoimage) {
            if (yzen_mime_type(strtok($urltoimage->getAttribute('href'), '?'))!="Unknown file type" && ! in_array($urltoimage->getAttribute('href'), $final)) {
                echo '<enclosure url="' . strtok($urltoimage->getAttribute('href'), '?') . '" type="'.yzen_mime_type(strtok($urltoimage->getAttribute('href'), '?')).'"/>'.PHP_EOL;
                $final[] = $urltoimage->getAttribute('href');
            }
        }
        $images = $dom->getElementsByTagName('img');
        $final = array();
        foreach ($images as $image) {
            if (! in_array($image->getAttribute('src'), $final)) {
                echo '<enclosure url="' . strtok($image->getAttribute('src'), '?') . '" type="'.yzen_mime_type(strtok($image->getAttribute('src'), '?')).'"/>'.PHP_EOL;
                $final[] = $image->getAttribute('src');
            }
        }
        ?>
        <?php
        if ($yzseodesc != 'disabled') {
            if ($yzseoplugin == 'Yoast SEO') {
                $temp = get_post_meta(get_the_ID(), "_yoast_wpseo_metadesc", true);
                $temp = apply_filters( 'yzen_the_excerpt', $temp );
                $temp = apply_filters( 'convert_chars', $temp );
                $temp = apply_filters( 'ent2ncr', $temp, 8 );
                if (!$temp) {$temp = yzen_the_excerpt_rss();}
                echo "<description><![CDATA[{$temp}]]></description>".PHP_EOL;
            }
            if ($yzseoplugin == 'All in One SEO Pack') {
                $temp = get_post_meta(get_the_ID(), "_aioseop_description", true);
                $temp = apply_filters( 'yzen_the_excerpt', $temp );
                $temp = apply_filters( 'convert_chars', $temp );
                $temp = apply_filters( 'ent2ncr', $temp, 8 );
                if (!$temp) {$temp = yzen_the_excerpt_rss();}
                echo "<description><![CDATA[{$temp}]]></description>".PHP_EOL;
            }
        } else { ?>
        <description><![CDATA[<?php echo yzen_the_excerpt_rss(); ?>]]></description>
        <?php } ?>
        <content:encoded><![CDATA[
       	<?php
        global $post;
        $tt = $post;
		$content = yzen_the_content_feed();
        $post = $tt;
        setup_postdata( $post );

        if ($yzexcludetags != 'disabled' && $yzexcludetagslist) {
            $content = yzen_strip_tags_without_content($content, $yzexcludetagslist);
        }
        if ($yzexcludetags2 != 'disabled' && $yzexcludetagslist2) {
            $content = yzen_strip_tags_with_content($content, $yzexcludetagslist2, true);
        }

        if ($yzthumbnail=="enabled" && has_post_thumbnail( get_the_ID() )) {
            $image_data = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID(),$yzselectthumb),$yzselectthumb);
            $content = '<p><img class="thumb" src="'. strtok(get_the_post_thumbnail_url(get_the_ID(),$yzselectthumb), '?') .'" alt="" width="'.$image_data[1].'" height="'.$image_data[2].'" /></p>' . PHP_EOL . $content;
        }

        //удаляем все unicode-символы (как невалидные в rss)
        $content = preg_replace('/[\x00-\x1F\x7F]/u', '', $content);

        //удаляем все атрибуты тега img кроме alt, src, width, height
        $content = yzen_strip_attributes($content,array('alt','src','width','height'));

        $content = wpautop($content);

        //удаляем разметку движка при использовании шорткода с подписью [caption] (в html4 темах)
        //и заменяем alt у тега img на текст подписи, установленной в редакторе
        $pattern = "/<div id=\"attachment(.*?)>(.*?)<img(.*?)alt=\"(.*?)\"(.*?) \/>(.*?)<\/p>\n<p class=\"wp-caption-text\">(.*?)<\/p>\n<\/div>/i";
        $replacement = '$2<img$3alt="$7"$5 />$6';
        $content = preg_replace($pattern, $replacement, $content);
        //удаляем ошметки шорткода [caption], если тег <div> удаляется в настройках плагина
        $pattern = "/<p class=\"wp-caption-text\">(.*?)<\/p>/i";
        $replacement = '';
        $content = preg_replace($pattern, $replacement, $content);

        //удаляем разметку движка при использовании шорткода с подписью [caption] (в html5 темах)
        //и заменяем alt у тега img на текст подписи, установленной в редакторе
        $pattern = "/<figure id=\"attachment(.*?)>(.*?)<img(.*?)alt=\"(.*?)\"(.*?) \/>(.*?)<figcaption class=\"wp-caption-text\">(.*?)<\/figcaption><\/figure>/i";
        $replacement = '$2<img$3alt="$7"$5 />$6';
        $content = preg_replace($pattern, $replacement, $content);

        //удаляем <figure>, если они изначально присутствуют в контенте записи
        $pattern = "/<figure(.*?)>(.*?)<img(.*?)>(.*?)<\/figure>/i";
        $replacement = '<img$3>';
        $content = preg_replace($pattern, $replacement, $content);

        //удаление тегов <p> у отдельно стоящих изображений
        $pattern = "/<p><img(.*?)\" \/><\/p>/i";
        $replacement = '<img$1" />';
        $content = preg_replace($pattern, $replacement, $content);

        //удаление тегов <p> у отдельно стоящих изображений, обернутых ссылкой
        $pattern = "/<p><a(.*?)><img(.*?)\" \/><\/a><\/p>/i";
        $replacement = '<a$1><img$2" /></a>';
        $content = preg_replace($pattern, $replacement, $content);

        //добавляем alt если его вообще нет в теге img
        $pattern = "/<img(?!([^>]*\b)alt=)([^>]*?)>/i";
        $replacement = '<img alt="'. get_the_title_rss() .'"$1$2>';
        $content = preg_replace( $pattern, $replacement, $content );

        //устанавливаем alt равным названию записи, если он пустой
        $pattern = "/<img alt=\"\" (.*?) \/>/i";
        $replacement = '<img alt="'.get_the_title_rss().'" $1 />';
        $content = preg_replace($pattern, $replacement, $content);

        $copyrighttext = ' <span class="copyright">'. $yzimgauthor .'</span>';
        if ($yzimgauthorselect == 'Отключить указание автора') {$copyrighttext = '';}
        $figcaptionopen = '<figcaption>'; $figcaptionclose = '</figcaption>';
        if ($yzfigcaption == "Отключить описания" && $yzimgauthorselect == 'Отключить указание автора') {
            $figcaptionopen = '';  $figcaptionclose = '';
        }

        //обрабатываем img теги и оборачиваем их тегами figure

        if ($yzfigcaption == "Использовать alt по возможности") {
             //оборачиваем img тегом figure и прописываем ему описание и авторство
             $pattern = "/<img alt=\"(.*?)\" src=\"(.*?)\" \/>/i";
             $replacement = '<figure><img src="$2" />'.$figcaptionopen.'$1'.$copyrighttext.$figcaptionclose.'</figure>';
             $content = preg_replace($pattern, $replacement, $content);
        }
        if ($yzfigcaption == "Использовать название записи") {
             //оборачиваем img тегом figure и прописываем ему описание и авторство
             $pattern = "/<img alt=\"(.*?)\" src=\"(.*?)\" \/>/i";
             $replacement = '<figure><img src="$2" />'.$figcaptionopen.get_the_title_rss() .$copyrighttext.$figcaptionclose.'</figure>';
             $content = preg_replace($pattern, $replacement, $content);
        }
        if ($yzfigcaption == "Отключить описания") {
             //оборачиваем img тегом figure и прописываем ему описание и авторство
             $pattern = "/<img alt=\"(.*?)\" src=\"(.*?)\" \/>/i";
             $replacement = '<figure><img src="$2" />'.$figcaptionopen.$copyrighttext.$figcaptionclose.'</figure>';
             $content = preg_replace($pattern, $replacement, $content);
        }

        if ($yzexcludecontent!='disabled' && $yzexcludecontentlist) {
            $textAr = explode("\n", trim($yzexcludecontentlist));
            $textAr = array_filter($textAr, 'trim');
            foreach ($textAr as $line) {
                $line = trim($line);
                $content = preg_replace('/'.$line.'/i','', $content);
            }
        }

        $content = preg_replace('/<p>https:\/\/youtu.*?<\/p>/i','', $content);
        $content = preg_replace('/<p>https:\/\/www.youtu.*?<\/p>/i','', $content);

		echo $content;

		?>]]></content:encoded>
    </item>
<?php endwhile; ?>
<?php wp_reset_postdata(); ?>
<?php wp_reset_query(); ?>
</channel>
</rss>
<?php }
//шаблон для RSS-ленты Яндекс.Дзен end

//функция установки корректного mime type для изображений begin
function yzen_mime_type($file) {
	$mime_type = array(
		"bmp"			=>	"image/bmp",
		"gif"			=>	"image/gif",
		"ico"			=>	"image/x-icon",
		"jpeg"			=>	"image/jpeg",
		"jpg"			=>	"image/jpeg",
		"png"			=>	"image/png",
		"psd"			=>	"image/vnd.adobe.photoshop",
		"svg"			=>	"image/svg+xml",
		"tiff"			=>	"image/tiff",
		"webp"			=>	"image/webp",
	);
	$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
	if (isset($mime_type[$extension])) {
		return $mime_type[$extension];
	} else {
		return "Unknown file type";
	}
}
//функция установки корректного mime type для изображений end

//установка правильного content type для ленты плагина begin
function yzen_feed_content_type( $content_type, $type ) {
    $yzen_options = get_option('yzen_options');
    if (!isset($yzen_options['yzrssname'])) {$yzen_options['yzrssname']="zen";update_option('yzen_options', $yzen_options);}
    if( $yzen_options['yzrssname'] == $type ) {
        $content_type = 'application/rss+xml';
    }
    return $content_type;
}
add_filter( 'feed_content_type', 'yzen_feed_content_type', 10, 2 );
//установка правильного content type для ленты плагина end

//функция формирования description в rss begin
function yzen_the_excerpt_rss() {
    $content = get_the_excerpt();
    $content = apply_filters('yzen_the_excerpt', $content);
    $content = apply_filters('convert_chars', $content);
    $content = apply_filters('ent2ncr', $content, 8);
    return $content;
}
//функция формирования description в rss end

//функция формирования content в rss begin
function yzen_the_content_feed() {
    $yzen_options = get_option('yzen_options');
    if ($yzen_options['yzexcerpt'] == 'enabled') {
        $content = '';
        if ( has_excerpt( get_the_ID() ) ) {
            $content = '<p>' . get_the_excerpt( get_the_ID() ) . '</p>';
        }
        $content .= apply_filters('the_content', get_post_field('post_content', get_the_ID()));
    } else {
        $content = apply_filters('the_content', get_post_field('post_content', get_the_ID()));
    }
    $content = apply_filters('yzen_the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);
    $content = apply_filters('wp_staticize_emoji', $content);
    $content = apply_filters('_oembed_filter_feed_content', $content);
    return $content;
}
//функция формирования content в rss end

//функция удаления тегов вместе с их контентом begin
function yzen_strip_tags_with_content($text, $tags = '', $invert = FALSE) {
    preg_match_all( '/<(.+?)[\s]*\/?[\s]*>/si', trim( $tags ), $tags_array );
	$tags_array = array_unique( $tags_array[1] );

	$regex = '';

	if ( count( $tags_array ) > 0 ) {
		if ( ! $invert ) {
			$regex = '@<(?!(?:' . implode( '|', $tags_array ) . ')\b)(\w+)\b[^>]*?(>((?!<\1\b).)*?<\/\1|\/)>@si';
			$text  = preg_replace( $regex, '', $text );
		} else {
			$regex = '@<(' . implode( '|', $tags_array ) . ')\b[^>]*?(>((?!<\1\b).)*?<\/\1|\/)>@si';
			$text  = preg_replace( $regex, '', $text );
		}
	} elseif ( ! $invert ) {
		$regex = '@<(\w+)\b[^>]*?(>((?!<\1\b).)*?<\/\1|\/)>@si';
		$text  = preg_replace( $regex, '', $text );
	}

	if ( $regex && preg_match( $regex, $text ) ) {
		$text = yzen_strip_tags_with_content( $text, $tags, $invert );
	}

	return $text;
}
//функция удаления тегов вместе с их контентом end

//функция удаления тегов без их контента begin
function yzen_strip_tags_without_content($text, $tags = '') {

    preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
    $tags = array_unique($tags[1]);

    if(is_array($tags) AND count($tags) > 0) {
        foreach($tags as $tag)  {
            $text = preg_replace("/<\\/?" . $tag . "(.|\\s)*?>/", '', $text);
        }
    }
    return $text;
}
//функция удаления тегов без их контента end

//функция принудительной установки header-тега X-Robots-Tag (решение проблемы с SEO-плагинами) begin
function yzen_index_follow_rss() {
    $yzen_options = get_option('yzen_options');
    if (!isset($yzen_options['yzrssname'])) {$yzen_options['yzrssname']="zen";update_option('yzen_options', $yzen_options);}
    if ( is_feed( $yzen_options['yzrssname'] ) ) {
        header( 'X-Robots-Tag: index, follow', true );
    }
}
add_action( 'template_redirect', 'yzen_index_follow_rss', 999999 );
//функция принудительной установки header-тега X-Robots-Tag (решение проблемы с SEO-плагинами) end

//функция удаления всех атрибутов тега img кроме указанных begin
function yzen_strip_attributes($s, $allowedattr = array()) {
  if (preg_match_all("/<img[^>]*\\s([^>]*)\\/*>/msiU", $s, $res, PREG_SET_ORDER)) {
   foreach ($res as $r) {
     $tag = $r[0];
     $attrs = array();
     preg_match_all("/\\s.*=(['\"]).*\\1/msiU", " " . $r[1], $split, PREG_SET_ORDER);
     foreach ($split as $spl) {
      $attrs[] = $spl[0];
     }
     $newattrs = array();
     foreach ($attrs as $a) {
      $tmp = explode("=", $a);
      if (trim($a) != "" && (!isset($tmp[1]) || (trim($tmp[0]) != "" && !in_array(strtolower(trim($tmp[0])), $allowedattr)))) {

      } else {
          $newattrs[] = $a;
      }
     }

     //сортировка чтобы alt был раньше src
     sort($newattrs);
     reset($newattrs);

     $attrs = implode(" ", $newattrs);
     $rpl = str_replace($r[1], $attrs, $tag);
     //заменяем одинарные кавычки на двойные
     $rpl = str_replace("'", "\"", $rpl);

     //добавляем закрывающий символ / если он отсутствует
     $rpl = str_replace("\">", "\" />", $rpl);
     //добавляем пробел перед закрывающим символом /
     $rpl = str_replace("\"/>", "\" />", $rpl);

     //удаляем двойные пробелы
     $rpl = str_replace("  ", " ", $rpl);

     //выносим атрибут height в конец тега
     $pattern = '/<img(.*?) height="(.*?)" (.*?) \/>/i';
     $replacement = '<img$1 $3 height="$2" />';
     $rpl = preg_replace($pattern, $replacement, $rpl);

     $s = str_replace($tag, $rpl, $s);
   }
  }

  return $s;
}
//функция удаления всех атрибутов тега img кроме указанных end

//функция установки новых опций при обновлении плагина у пользователей begin
function yzen_set_new_options() {
$yzen_options = get_option('yzen_options');
if (!isset($yzen_options['yzthumbnail'])) {$yzen_options['yzthumbnail']="disabled";update_option('yzen_options', $yzen_options);}
if (!isset($yzen_options['yzselectthumb'])) {$yzen_options['yzselectthumb']="";update_option('yzen_options', $yzen_options);}
if (!isset($yzen_options['yzseodesc'])) {$yzen_options['yzseodesc']="disabled";update_option('yzen_options', $yzen_options);}
if (!isset($yzen_options['yzseoplugin'])) {$yzen_options['yzseoplugin']="Yoast SEO";update_option('yzen_options', $yzen_options);}
if (!isset($yzen_options['yzexcludetags'])) {$yzen_options['yzexcludetags']="disabled";update_option('yzen_options', $yzen_options);}
if (!isset($yzen_options['yzexcludetagslist'])) {$yzen_options['yzexcludetagslist']="<div>";update_option('yzen_options', $yzen_options);}
if (!isset($yzen_options['yzexcludetags2'])) {$yzen_options['yzexcludetags2']="enabled";update_option('yzen_options', $yzen_options);}
if (!isset($yzen_options['yzexcludetagslist2'])) {$yzen_options['yzexcludetagslist2']="<iframe>,<script>,<ins>,<style>,<object>";update_option('yzen_options', $yzen_options);}
if (!isset($yzen_options['yzexcludecontent'])) {$yzen_options['yzexcludecontent']="enabled";update_option('yzen_options', $yzen_options);}
if (!isset($yzen_options['yzexcludecontentlist'])) {$yzen_options['yzexcludecontentlist']=esc_textarea("<!--more-->\n<p><\/p>\n<p>&nbsp;<\/p>");update_option('yzen_options', $yzen_options);}
if (!isset($yzen_options['yzmediascope'])) {$yzen_options['yzmediascope']="";update_option('yzen_options', $yzen_options);}
if (!isset($yzen_options['yzqueryselect'])) {$yzen_options['yzqueryselect']="Все таксономии, кроме исключенных";update_option('yzen_options', $yzen_options);}
if (!isset($yzen_options['yztaxlist'])) {$yzen_options['yztaxlist']="";update_option('yzen_options', $yzen_options);}
if (!isset($yzen_options['yztaxnewslist'])) {$yzen_options['yztaxnewslist']="";update_option('yzen_options', $yzen_options);}
if (!isset($yzen_options['yzaddtaxlist'])) {$yzen_options['yzaddtaxlist']="";update_option('yzen_options', $yzen_options);}
if (!isset($yzen_options['yzexcerpt'])) {$yzen_options['yzexcerpt']="disabled";update_option('yzen_options', $yzen_options);}
if (!isset($yzen_options['yzexcludedefault'])) {$yzen_options['yzexcludedefault']="disabled";update_option('yzen_options', $yzen_options);}
}
//функция установки новых опций при обновлении плагина у пользователей end

//функция исключения записей из ленты по умолчанию begin
function yzen_new_post( $post_id, $post, $update ) {
    $yzen_options = get_option('yzen_options');
    if ( $yzen_options['yzexcludedefault'] == 'disabled' )
        return;

    if ( !get_post_meta( $post_id, 'yzrssenabled_meta_value', true ) ) {
        update_post_meta( $post_id, 'yzrssenabled_meta_value', 'yes' );
    }
}
add_action( 'save_post', 'yzen_new_post', 10, 3 );
//функция исключения записей из ленты по умолчанию end
