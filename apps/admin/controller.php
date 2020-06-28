<?php
    error_reporting(0);
class Admin_Controller extends Controller {

    public function __construct()
    {
        @session_start();
        $u = \Users\get();

        if(!$u || !$u['is_admin']) {
            Http::redirect('/');
        }
    }

    public function main() {
        render('main', array());
    }

    public function tickets() {

        Import::private_module('xcrud');

        $xcrud = Xcrud::get_instance();
        $xcrud->table('tickets');

        $xcrud->table_name('Билеты');
        $xcrud->columns('test_number,variant_number,question,answer');
        $xcrud->fields('test_number,variant_number,question,answer');

        $xcrud->label('test_number', 'Номер билета');
        $xcrud->label('variant_number', 'Номер варианта');
        $xcrud->label('question', 'Вопрос (для скачивания)');
        $xcrud->label('answer', 'Ответ (для проверки)');

        $xcrud->change_type('question', 'textarea');
        $xcrud->change_type('answer', 'textarea');

        $xcrud->order_by('test_number,variant_number', 'ASC');

        $xcrud->limit('30');
        $xcrud->limit_list('30, 50, 100');

        $xcrud->unset_view();
        $xcrud->unset_limitlist();
        $xcrud->unset_csv();
        $xcrud->unset_print();
        $xcrud->remove_confirm(true);

        render('xcrud', array(
            'xcrud' => $xcrud->render()
        ));
    }

    public function answers() {

        Import::private_module('xcrud');

        $xcrud = Xcrud::get_instance();
        $xcrud->table('answers');

        $xcrud->table_name('Присланные ответы');
        $xcrud->columns('user_id,test_number,variant_number,attempt,answer,status');

        $xcrud->label('user_id', 'Пользователь');
        $xcrud->label('test_number', 'Номер билета');
        $xcrud->label('variant_number', 'Номер варианта');
        $xcrud->label('attempt', 'Попытка #');
        $xcrud->label('answer', 'Ответ');
        $xcrud->label('status', 'Правильно?');

        $xcrud->relation('user_id','user','id','username');
        $xcrud->change_type('attempt', 'number');

        $xcrud->column_callback('answer', 'answer_render');
        $xcrud->column_callback('status', 'status_render');

        $xcrud->order_by('date', 'DESC');

        $xcrud->limit('30');
        $xcrud->limit_list('30, 50, 100');

        $xcrud->unset_remove();
        $xcrud->unset_edit();
        $xcrud->unset_add();
        $xcrud->unset_view();
        $xcrud->unset_limitlist();
        $xcrud->unset_csv();
        $xcrud->unset_print();
        $xcrud->remove_confirm(true);

        render('xcrud', array(
            'xcrud' => $xcrud->render()
        ));
    }

    public function answers_download($args) {
        // username или id _ + variant 

        $aid = $args['1'];

        $answer = R::findOne('answers', "`id` = ?", array($aid));
        $user = R::findOne('user', $answer->user_id);

        $filename = $user->username . '_test_' . $answer->test_number . '_var_' . $answer->variant_number . '.txt';

        // show question
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename='.$filename);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . mb_strlen($answer->answer, '8bit'));
        header("Content-Type: text/plain");

        echo $answer->answer;
        exit();
    }

    public function categories()
    {
        Import::private_module('xcrud');


        $xcrud = Xcrud::get_instance();
        $xcrud->table('categories');

        $xcrud->table_name('КАТЕГОРИИ');

        $xcrud->columns('title,slug_url,vktag,sort,updates_required');
        $xcrud->relation('parent_id','categories','id','title');

        $xcrud->fields('title,slug_url,desc,keywords,image_icon,vktag,sort,parent_id,updates_required');

        $xcrud->label('title', 'Название');
        $xcrud->label('slug_url', 'ЧПУ');
        $xcrud->label('desc', 'Мета описание (SEO)');
        $xcrud->label('keywords', 'Мета ключи (SEO)');
        $xcrud->label('image_icon', 'Своя иконка (base64)');
        $xcrud->label('vktag', 'Тег ВК');
        $xcrud->label('sort', 'Сортировка');
        $xcrud->label('parent_id', 'Родительская категория');
        $xcrud->label('updates_required', 'Частота проверки обновлений');

        $xcrud->order_by('sort', 'ASC');
        $xcrud->column_pattern('title', '<img src="{image_icon}" width="16px"/> {title} <sup>{id}</sup></strong>');
        $xcrud->change_type('image_icon', 'textarea');
        $xcrud->change_type('desc', 'textarea');
        $xcrud->change_type('keywords', 'textarea');

        /**
         * Подкатегории.
         */
        $subcats = $xcrud->nested_table('subcats', 'id', 'categories', 'parent_id');
        $subcats->table_name('Подкатегории');
        // $subcats->language('ru');
        $subcats->limit('30');
        $subcats->limit_list('30, 50, 100');

        $subcats->columns('title,slug_url,vktag,sort');
        $subcats->fields('title,slug_url,desc,keywords,vktag,sort');
        $subcats_definitions = array(
            'title' => 'Название',
            'slug_url' => 'ЧПУ',
            'desc' => 'Описание',
            'keywords' => 'Ключевые слова (через запятую)',
            'vktag' => 'Тег ВК',
            'sort' => 'Сортировка'
        );
        $subcats->order_by('sort', 'ASC');
        $subcats->column_pattern('title', '{title} <sup>{id}</sup></strong>');
        // $subcats->change_type('image_icon', 'image', '', array('path' => STATIC_DIR . '/uploads/icons'));
        $subcats->change_type('image_icon', 'textarea');
        $subcats->change_type('desc', 'textarea');
        $subcats->change_type('keywords', 'textarea');

        $subcats->unset_view();
        $subcats->unset_limitlist();
        $subcats->unset_csv();
        $subcats->unset_print();
        $subcats->remove_confirm(true);

        /**
         * Подподкатегории.
         */
        $subsubcats = $subcats->nested_table('subsubcats', 'id', 'categories', 'parent_id');
        $subsubcats->table_name('Подподкатегории');
        // $subsubcats->language('ru');
        $subsubcats->limit('30');
        $subsubcats->limit_list('30, 50, 100');

        $subsubcats->columns('title,vktag,sort');
        $subsubcats->fields('title,vktag,sort');
        $subsubcats_definitions = array(
            'title' => 'Название',
            'icon' => 'Иконка (Font Awesome)',
            'image_icon' => 'Своя иконка',
            'vktag' => 'Тег ВК',
            'sort' => 'Сортировка'
        );

        $subsubcats->unset_view();
        $subsubcats->unset_limitlist();
        $subsubcats->unset_csv();
        $subsubcats->unset_print();
        $subsubcats->remove_confirm(true);

        $subsubcats->order_by('sort', 'ASC');
        $subsubcats->column_pattern('title', '{title} <sup>{id}</sup></strong>');
        // $subsubcats->change_type('image_icon', 'image', '', array('path' => STATIC_DIR . '/uploads/icons'));
        $subsubcats->change_type('image_icon', 'file', '', array('path' => '../../../../static/uploads/icons'));

        foreach ($subcats_definitions as $key => $def) {
            $subcats->label($key, $def);
        }

        $xcrud->unset_view();
        $xcrud->unset_limitlist();
        $xcrud->unset_csv();
        $xcrud->unset_print();
        $xcrud->remove_confirm(true);

        $xcrud->limit('30');
        $xcrud->limit_list('30, 50, 100');
        $xcrud->where('parent_id IS NULL');
        render('xcrud', array(
            'xcrud' => $xcrud->render(),
            'general_stats' => $this->general_stats()
        ));
    }

    public function reports()
    {
        Import::private_module('xcrud');


        $xcrud = Xcrud::get_instance();
        $xcrud->table('reports');

        $xcrud->table_name('РЕПОРТЫ');

        $xcrud->columns('materials_id,pubdate,user_ip,status');
        $xcrud->relation('materials_id','materials','id','title');

        // $xcrud->fields('title,slug_url,desc,keywords,image_icon,vktag,sort,parent_id');

        $xcrud->change_type('pubdate', 'datetime', '');

        $xcrud->label('materials_id', 'Материал');
        $xcrud->label('pubdate', 'Дата репорта');
        $xcrud->label('user_ip', 'IP юзера');
        $xcrud->label('status', 'Статус');

        $xcrud->order_by('pubdate', 'ASC');

        $xcrud->unset_view();
        $xcrud->unset_limitlist();
        $xcrud->unset_csv();
        $xcrud->unset_print();
        $xcrud->remove_confirm(true);

        $xcrud->limit('30');
        $xcrud->limit_list('30, 50, 100');

        render('xcrud', array(
            'xcrud' => $xcrud->render(),
            'general_stats' => $this->general_stats()
        ));
    }

    private function url_slug($str, $options = array()) {
      // Make sure string is in UTF-8 and strip invalid UTF-8 characters
      $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());
      
      $defaults = array(
        'delimiter' => '-',
        'limit' => null,
        'lowercase' => true,
        'replacements' => array(),
        'transliterate' => false,
      );
      
      // Merge options
      $options = array_merge($defaults, $options);
      
      $char_map = array(
        // Latin
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C', 
        'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 
        'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O', 
        'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH', 
        'ß' => 'ss', 
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 
        'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o', 
        'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th', 
        'ÿ' => 'y',
        // Latin symbols
        '©' => '(c)',
        // Greek
        'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
        'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
        'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
        'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
        'Ϋ' => 'Y',
        'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
        'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
        'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
        'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
        'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
        // Turkish
        'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
        'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g', 
        // Russian
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
        'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
        'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
        'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
        'Я' => 'Ya',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
        'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
        'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
        'я' => 'ya',
        // Ukrainian
        'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
        'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
        // Czech
        'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U', 
        'Ž' => 'Z', 
        'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
        'ž' => 'z', 
        // Polish
        'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z', 
        'Ż' => 'Z', 
        'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
        'ż' => 'z',
        // Latvian
        'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N', 
        'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
        'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
        'š' => 's', 'ū' => 'u', 'ž' => 'z'
      );
      
      // Make custom replacements
      $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);
      
      // Transliterate characters to ASCII
      if ($options['transliterate']) {
        $str = str_replace(array_keys($char_map), $char_map, $str);
      }
      
      // Replace non-alphanumeric characters with our delimiter
      $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
      
      // Remove duplicate delimiters
      $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
      
      // Truncate slug to max. characters
      $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');
      
      // Remove delimiter from ends
      $str = trim($str, $options['delimiter']);
      
      return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
    }

}