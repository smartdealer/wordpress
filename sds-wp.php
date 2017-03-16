<?php

/*
  Plugin Name: Smart Dealer
  Plugin URI: http://smartdealership.com.br/api.html
  Description: Plugin de integração com o Smart Dealer (compatível com wordpress 3 ou superior) - Fev/2017
  Version: 1.1
  Author: Patrick Otto <patrick@smartdealership.com.br>, Jean Carlos dos Santos <jean@smartdealership.com.br>
  Author URI: http://smartdealer.com.br
 */

class SmartDealer extends stdClass {

    const CONFIG_TITLE = 'Integração com o Smart Dealer';
    const MENU_NAME = 'Smart Dealer';
    const NUSOAP_PATH = 'nusoap/nusoap.php';
    const IMG_PLUGIN_URL = 'getimage';
    const IMG_EXT = '.jpg';
    const PAGER_TOTAL_ROWS = 12;
    const PAGER_TOTAL_PAGES = 10;
    const US = '/';
    const DS = DIRECTORY_SEPARATOR;

    /**
     * Static vars
     */
    private static $slug, $link_modal, $img_owner;

    /**
     * Proteceted vars
     */
    protected $ws, $ws_path, $ws_method, $ws_modal, $instance, $data = array(), $log = array();

    /**
     * Public vars
     */
    public $boot_status;

    /**
     * WS request schema 
     */
    public $schema = array(
        '[smartdealer_novos]' => array(
            'include' => 'novos.php',
            'method' => 'CarrosNovos',
            'modal' => 'novos',
            'child' => 'novo'
        ),
        '[smartdealer_usados]' => array(
            'include' => 'usados.php',
            'method' => 'CarrosUsados',
            'modal' => 'usados',
            'child' => 'seminovo'
        ),
        '[smartdealer_corsia]' => array(
            'include' => 'estoque-futuro.php',
            'method' => 'CarrosCorsia',
            'modal' => 'corsia',
            'child' => 'corsia'
        ),
        '[smartdealer_veiculo_novo]' => array(
            'include' => 'novo.php',
            'method' => 'CarroNovo',
            'modal' => 'novos'
        ),
        '[smartdealer_veiculo_usado]' => array(
            'include' => 'usado.php',
            'method' => 'CarroUsado',
            'modal' => 'usados'
        ),
        '[smartdealer_veiculo_corsia]' => array(
            'include' => 'corsia.php',
            'method' => 'CarroCorsia',
            'modal' => 'corsia'
        )
    );

    public function __construct() {

        // set path
        $this->ws_path = (stristr(get_option('sds_instancia'), '://')) ? get_option('sds_instancia') : 'http://' . get_option('sds_instancia') . '.smartdealer.com.br/webservice/core.php?wsdl';

        // set anchor
        $this->instance = $this;
    }

    public function addRoutes() {
        add_rewrite_rule('^novo/([^/]+)/([^/]+)/?', 'index.php?page_id=' . get_option('sds_path_novos') . '&veiculo=$matches[2]', 'top');
        add_rewrite_rule('^seminovo/([^/]+)/([^/]+)/?', 'index.php?page_id=' . get_option('sds_path_usados') . '&veiculo=$matches[2]', 'top');
        add_rewrite_rule('^corsia/([^/]+)/([^/]+)/?', 'index.php?page_id=' . get_option('sds_path_corsia') . '&veiculo=$matches[2]', 'top');
    }

    public function flush() {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }

    public function addParams($query_vars) {
        $query_vars[] = 'veiculo';
        return $query_vars;
    }

    public function enable() {
        update_option('sds_instancia', '');
        update_option('sds_usuario', '');
        update_option('sds_senha', '');
        update_option('sds_path_novos', '');
        update_option('sds_path_usados', '');
        update_option('sds_path_corsia', '');

        // set folder permissions
        try {
            chmod(self::pathPlugin() . DIRECTORY_SEPARATOR . 'getimage' . DIRECTORY_SEPARATOR . 'cache', 775);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function disable() {
        delete_option('sds_instancia');
        delete_option('sds_usuario');
        delete_option('sds_senha');
        delete_option('sds_path_novos');
        delete_option('sds_path_usados');
        delete_option('sds_path_corsia');
    }

    public function createMenu() {
        add_menu_page(self::CONFIG_TITLE, self::MENU_NAME, 10, 'sds-wp/sds-wp-config.php', null, plugins_url() . '/sds-wp/img/favicon.ico');
    }

    static function the_slug() {
        self::$slug = basename(get_permalink());
        do_action('before_slug', self::$slug);
        self::$slug = apply_filters('slug_filter', self::$slug);
        do_action('after_slug', self::$slug);
        return self::$slug;
    }

    static function the_id() {
        $a = urldecode(trim(parse_url(get_query_var('veiculo'), PHP_URL_PATH), '/'));
        $c = base64_decode($a);
        return ($a === base64_encode($c)) ? $c : null;
    }

    static function encodeData($a) {
        return base64_encode($a);
    }

    static function decodeData($a) {
        return base64_decode($a);
    }

    static function link($a, $b = null) {
        $b = preg_replace('/\s+/', '-', self::prepareString($b));
        return site_url() . '/' . self::$link_modal . (($b) ? '/' . $b : '') . '/' . urlencode(base64_encode($a));
    }

    public function isIncluded($fileName) {
        return in_array(__DIR__ . self::DS . 'template' . self::DS . $fileName, get_included_files());
    }

    public static function formAction() {
        return current(explode('?', getenv('REQUEST_URI')));
    }

    public static function pathPlugin() {
        return substr(strrchr(dirname(__FILE__), DIRECTORY_SEPARATOR), 1) . DIRECTORY_SEPARATOR . basename(__FILE__);
    }

    public static function url($a) {
        return plugins_url() . '/sds-wp/' . $a;
    }

    public static function formGet($a, $b = null) {
        return ($c = filter_input(INPUT_GET, $a, FILTER_SANITIZE_STRING)) ? $c : $b;
    }

    public function connect() {

        // add plugin
        require_once self::NUSOAP_PATH;

        // instance nusoap
        $this->ws = new \nusoap_client($this->ws_path, true);

        // debug (log error)
        $this->logError($this->ws->getError());

        // set credentials
        $this->ws->setCredentials(get_option('sds_usuario'), get_option('sds_senha'));

        // debug (log error)
        $this->logError($this->ws->getError());

        // boot status
        return (bool) (!$this->logError());
    }

    public function logError($a = null) {
        if ($a) {
            $this->log[] = $a;
        } else {
            return $this->log;
        }
    }

    public function setMethod($a, $b, $c = null) {
        $this->ws_method = $a;
        $this->ws_modal = $b;

        // set modal (for static4)
        self::$link_modal = ($c) ? $c : '';
    }

    static public function show_404() {
        if (is_page()) {
            status_header(404);
            include(get_query_template('header'));
            include(get_query_template('404'));
            include(get_query_template('footer'));
            exit(0);
        }
    }

    public function getData() {

        // set params
        $prm = array('parametrospage' => array(
                'pp' => self::formGet('pp', 1),
                'qtd_por_pp' => self::PAGER_TOTAL_ROWS,
                'marca' => self::formGet('marca', ''),
                'familia' => self::formGet('familia', ''),
                'modelo' => self::formGet('modelo', ''),
                'cor' => self::formGet('cor', ''),
                'combustivel' => self::formGet('combustivel', ''),
                'ano_min' => (int) self::formGet('ano_min', 0),
                'ano_max' => (int) self::formGet('ano_max', 0),
                'preco_min' => (int) self::formGet('preco_min', 0),
                'preco_max' => (int) self::formGet('preco_max', 0),
                'campo_ordenador' => self::formGet('campo_ordenador', ''),
                'sentido_ordenacao' => self::formGet('sentido_ordenacao', ''),
                'query' => self::formGet('query', '')
        ));

        $prm['parametrospage'] = array_filter($prm['parametrospage']);

        // call ws
        $this->data = $this->ws->call($this->ws_method, $prm);

        // debug (log error)
        $this->logError($this->ws->getError());

        // return
        return ($this->data) ? $this->data : array();
    }

    public function getRow() {

        $id = self::the_id();

        // valid
        if (!$id) {
            self::show_404();
        }

        // set params
        $prm = array(
            'chassi' => $id,
            'id' => $id,
            'placa' => $id
        );

        // call ws
        $this->data = $this->ws->call($this->ws_method, $prm);

        // debug (log error)
        $this->logError($this->ws->getError());

        // return
        return ($this->data) ? $this->data : array();
    }

    public function getMarks() {

        // se params
        $prm = array('parametrosbusca' => array(
                'modo' => $this->ws_modal
        ));

        if (self::formGet('estoque', '')) {
            $prm['parametrosbusca']['estoque'] = self::formGet('estoque');
        }

        if (self::formGet('estoque', '')) {
            $prm['parametrosbusca']['filial'] = self::formGet('filial');
        }

        // call ws
        $this->marks = $this->ws->call('GetMarcas', $prm);

        // debug (log error)
        $this->logError($this->ws->getError());

        // return
        return ($this->marks && !$this->ws->getError()) ? $this->marks : array();
    }

    public function getModels() {

        // se params
        $prm = array('parametrosbusca' => array(
                'modo' => $this->ws_modal
        ));

        if (self::formGet('estoque', '')) {
            $prm['parametrosbusca']['estoque'] = self::formGet('estoque');
        }

        if (self::formGet('estoque', '')) {
            $prm['parametrosbusca']['filial'] = self::formGet('filial');
        }

        // call ws
        $this->models = $this->ws->call('GetModelos', $prm);

        // debug (log error)
        $this->logError($this->ws->getError());

        // return
        return ($this->models && !$this->ws->getError()) ? $this->models : array();
    }

    public function getFamilies() {

        // se params
        $prm = array('parametrosbusca' => array(
                'modo' => $this->ws_modal
        ));

        if (self::formGet('estoque', '')) {
            $prm['parametrosbusca']['estoque'] = self::formGet('estoque');
        }

        if (self::formGet('estoque', '')) {
            $prm['parametrosbusca']['filial'] = self::formGet('filial');
        }

        // call ws
        $this->families = $this->ws->call('GetFamilias', $prm);

        // debug (log error)
        $this->logError($this->ws->getError());

        // return
        return ($this->families && !$this->ws->getError()) ? $this->families : array();
    }

    public function getColors() {

        // se params
        $prm = array('parametrosbusca' => array(
                'modo' => $this->ws_modal
        ));

        if (self::formGet('estoque', '')) {
            $prm['parametrosbusca']['estoque'] = self::formGet('estoque');
        }

        if (self::formGet('estoque', '')) {
            $prm['parametrosbusca']['filial'] = self::formGet('filial');
        }

        // call ws
        $this->colors = $this->ws->call('GetCores', $prm);

        // debug (log error)
        $this->logError($this->ws->getError());

        // return
        return ($this->colors && !$this->ws->getError()) ? $this->colors : array();
    }

    public function getFuels() {

        // se params
        $prm = array('parametrosbusca' => array(
                'modo' => $this->ws_modal
        ));

        if (self::formGet('estoque', '')) {
            $prm['parametrosbusca']['estoque'] = self::formGet('estoque');
        }

        if (self::formGet('estoque', '')) {
            $prm['parametrosbusca']['filial'] = self::formGet('filial');
        }

        // call ws
        $this->fuels = $this->ws->call('GetCombustiveis', $prm);

        // debug (log error)
        $this->logError($this->ws->getError());

        // return
        return ($this->fuels && !$this->ws->getError()) ? $this->fuels : array();
    }

    public function getTotais() {

        // se params
        $prm = array('parametrosbusca' => array(
                'modo' => $this->ws_modal
        ));

        if (self::formGet('estoque', '')) {
            $prm['parametrosbusca']['estoque'] = self::formGet('estoque');
        }

        if (self::formGet('estoque', '')) {
            $prm['parametrosbusca']['filial'] = self::formGet('filial');
        }

        // call ws
        $this->totals = $this->ws->call('GetTotais', $prm);

        // debug (log error)
        $this->logError($this->ws->getError());

        // return
        return ($this->totals && !$this->ws->getError()) ? $this->totals : array();
    }

    public function addLead($keys, $ignore = array(), &$ret) {

        // valid method
        if (stristr($_SERVER['REQUEST_METHOD'], 'POST')) {

            // force remove unsed
            $keys = array_diff_key($keys, array_flip((array) $ignore));

            // filter input
            $in = ($in = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING)) ? array_map(function($a) {
                        return (is_array($a)) ? null : str_replace('\r\n', '', nl2br(trim(strip_tags(addslashes($a)))));
                    }, array_intersect_key($in, array_flip($keys))) : array();

            // valid fields
            $valid = ($keys && count($keys) === count($in));

            // validate 
            if (!$valid) {
                return;
            }

            // se params
            $prm = array('mensagem' => array(
                    'carro_id' => self::decodeData($in['id']),
                    'nome' => $in['nome'],
                    'e_mail' => $in['email'],
                    'telefone' => $in['telefone'],
                    'mensagem' => $in['cidade'] . '/' . $in['estado'] . ' ' . $in['mensagem'],
                    'modal' => $this->ws_modal,
                    'origem' => 'site'
            ));

            // call ws
            $this->response = $this->ws->call('EuQuero', $prm);

            // debug (log error)
            $this->logError($this->ws->getError());

            // status
            $ret = (bool) ($this->response === 'sucesso' && !$this->ws->getError());
        }
    }

    static public function prepareName($str, $filter = true) {

        $pat = array('/\&[A-Z]+\;/i', '/[^A-Za-z0-9\-\. ]\s/');

        // especial filter (remove catalog)
        if ($filter) {
            $pat[-1] = '/^\w(\W|\s)/';
        }

        // prepare string
        return trim(ucwords(preg_replace($pat, ' ', html_entity_decode($str))));
    }

    static public function prepareString($str, $lower = true) {

        // check encode
        $str = self::prepareCharset($str);

        // crate patterns
        $patterns = array(
            'a' => '/À|Á|Â|Ã|Ä|Å|à|á|â|ã|ä|å/m',
            'c' => '/Ç|ç/m',
            'e' => '/È|É|Ê|Ë|è|é|ê|ë/m',
            'i' => '/Ì|Í|Î|Ï|ì|í|î|ï/m',
            'n' => '/Ñ|ñ/m',
            'o' => '/Ò|Ó|Ô|Õ|Ö|ò|ó|ô|õ|ö/m',
            'u' => '/Ù|Ú|Û|Ü|ù|ú|û|ü/m',
            'y' => '/Ý|ý|ÿ/m',
            '' => '/[\"\'\/]/m',
            '' => '/\//m',
            '' => '/\r|\t|\n/',
            ' ' => '/([^A-Za-z0-9_\.,- ])+/m',
            ' ' => '/\s\s?\s?/m',
            '' => '/\(|\)|\[|\]/m'
        );

        // find and replace special and invalid chars
        $str = preg_replace($patterns, array_keys($patterns), $str);

        // convert to standard format
        $str = html_entity_decode(str_replace('/', ' ', stripslashes($str)));
        $str = ($lower) ? strtolower($str) : strtoupper($str);

        // return
        return trim($str);
    }

    static public function prepareCharset($str) {

        // set default encode
        mb_internal_encoding('UTF-8');

        // pre filter
        if (empty($str)) {
            return $str;
        }

        // get charset
        $charset = mb_detect_encoding($str, array('ISO-8859-1', 'UTF-8', 'ASCII'));
        $is_utf8 = (preg_match('!!u', $str));

        // convert
        if (stristr($charset, 'utf')) {
            $str = iconv('ISO-8859-1', 'UTF-8', utf8_decode($str));
        } elseif (stristr($charset, 'ISO-8859-1')) {
            $str = utf8_encode($str);
        } elseif (!$is_utf8) {
            $str = iconv($charset, "UTF-8", $str);
        }

        // remove BOM
        $str = (stristr($str, "%C2%81")) ? urldecode(str_replace("%C2%81", '', urlencode($str))) : $str;

        // prepare string
        return trim($str);
    }

    static public function prepareFuel($a) {
        return preg_match('/(gas([a-z]+)?|alc([a-z]+)?|etan([a-z]+)?).*(gas([a-z]+)?|alc([a-z]+)?|etan([a-z]+)?)/i', $a) ? 'Flex' : ucfirst(strtolower($a));
    }

    public static function array_column($array, $column, $key = null, $pkey = false) {
        // init vars
        $newArray = array();
        $keyArray = array();

        // valid param
        if (!is_array($array))
            return $newArray;

        // find column
        foreach ($array as $k => $value) {

            if ($column && isset($value[$column]))
                $newArray[] = $value[$column];

            if ($key && (isset($value[$key][0]) or $pkey))
                $keyArray[] = ($pkey) ? $k : $value[$key];
        }

        // add keys
        if (($keyArray && $key and ( !is_array($key) && !is_object($key)) or $pkey) && count($keyArray) === count($newArray))
            $newArray = array_combine((array) $keyArray, (array) $newArray);

        // return
        return $newArray;
    }

    public static function img($a, $m, $c, $w = 500, $s = 1, $u = 0, $prm = '') {

        // detect and cache owner
        self::$img_owner = (self::$img_owner) ? self::$img_owner : ((($r = parse_url(get_option('sds_instancia'))) && !empty($r['host'])) ? strtok($r['host'], '.') : ((isset($r['path'])) ? $r['path'] : $r));

        // return
        return self::url(self::IMG_PLUGIN_URL . '/' . (($u) ? $a . self::US . self::$img_owner . self::US . $w . self::US . $s : urlencode($m) . self::US . urlencode($c) . self::US . self::$img_owner . self::US . $w . self::US . $s ) . self::IMG_EXT . (($prm) ? '?' . trim(((is_string($prm)) ? $prm : http_build_query($prm)), '?\//& ') : ''));
    }

    static public function prepareYear($year = 0, $used = true) {

        // year tratament and validation
        $year = ($year === 00) ? substr(date('Y'), 0, 1) . '000' : ((empty($year)) ? date('Y') : $year);

        // return yeaer wicth four digits
        return (int) (strlen($year) <= 2) ? (($year > (date('y') + 2) and ( $year <= 99) and $used) ? $year + 1900 : $year + ((int) substr(date('Y'), 0, 2) . "00")) : $year;
    }

    static public function preparePrice($a, $b = 0, $c = 2) {
        // prepare float number 
        return (((int) str_replace(',', '', $a)) < 10) ? 'Sob Consulta' : ((($b) ? 'R$ ' : '') . number_format($a, $c, ',', '.'));
    }

    public function getPager($data) {

        // valid data
        $a = (!empty($data) && is_array($data) && array_key_exists(0, $data)) ? current($data) : array();

        // get pager params
        $input = (array) (filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING));
        $page = (isset($input['pp'])) ? $input['pp'] : 1;
        $pages = (isset($a['p_total'])) ? $a['p_total'] : 0;
        $tot = (isset($a['total'])) ? $a['total'] : 1;

        if (isset($input['pp']))
            unset($input['pp']);

        $prm = '?' . (($input) ? http_build_query(array_unique(array_filter($input))) . '&' : '');
        $link = self::formAction() . $prm;

        // init
        $pager = array();

        // fill next shortcuts
        if ($page != 1):
            $pager[] = array(
                'link' => $link . 'pp=1',
                'text' => 'Primeira',
                'atual' => '0'
            );
            $pager[] = array(
                'link' => $link . 'pp=' . ($page - 1),
                'text' => ' &laquo;',
                'atual' => '0'
            );
        endif;

        // add middle pages
        for ($k = 1; $k <= $pages; ++$k):

            // match state
            $active = ($k == $page) ? 1 : 0;
            $diff = ceil(self::PAGER_TOTAL_PAGES / 2) - 1;
            $min = $page - $diff;
            $max = $page + $diff;

            // add 
            if ($k > $min and $k < $max)
                $pager[] = array(
                    'link' => $link . 'pp=' . $k,
                    'text' => $k,
                    'atual' => $active
                );
        endfor;

        // add end shotcuts
        if ($pages > $page):
            $pager[] = array(
                'link' => $link . 'pp=' . ($page + 1),
                'text' => '&raquo;',
                'atual' => '0'
            );

            $pager[] = array(
                'link' => $link . 'pp=' . $pages,
                'text' => 'Ultima',
                'atual' => '0'
            );
        endif;

        // return
        return $pager;
    }

    public static function getURL() {
        return implode('', array('http://', $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']));
    }

    static public function prepareText($str, $strip = 0) {

        $str = self::prepareCharset($str);

        // return
        return trim(html_entity_decode($str));
    }

    public static function prepareOptString($str) {

        // trataments, format string
        $str = self::prepareString(html_entity_decode($str));

        // crate patterns
        $patterns = array(
            '' => '/([a-z0-9]+\s+[-]\s?)+/i',
            ';' => '/[;,|]+/i'
        );

        // find and replace special and invalid charsopts
        $str = preg_replace($patterns, array_keys($patterns), $str);
        $str = implode(';', array_unique(explode(';', $str), 1));

        // return
        return trim($str, '; ');
    }

    static public function getSocial() {
        return get_option('sds_instancia');
    }

    // the compile
    function compile($a) {
        global $api;

        // set data
        $api->_tpl = $a;

        // compile data (find flags)
        if ($api->boot_status) {
            foreach ($api->schema AS $k => $b) {
                if (stristr($api->_tpl, $k) && !empty($b['method']) && !empty($b['modal']) && !empty($b['include']) && !$api->isIncluded($b['include'])) {

                    ob_start(null, 0);

                    $api->setMethod($b['method'], $b['modal'], (isset($b['child'])) ? $b['child'] : null);
                    $api->connect($b['method']);

                    // include tpl
                    include('template/' . $b['include']);

                    // collect data
                    $c = ob_get_clean();

                    // replace data
                    $api->_tpl = ($k && !empty($c)) ? (str_ireplace($k, $c, strip_tags($api->_tpl, 'p br'))) : $api->_tpl;
                }
            }
        }

        // return
        return $api->_tpl;
    }

    public function ready() {
        global $api;
        $api->boot_status = true;
    }

}

// load instance
$api = new SmartDealer();

// add content filter
add_filter('the_content', array('SmartDealer', 'compile'));

// add admin
add_action('admin_menu', array('SmartDealer', 'createMenu'));

// add custom routes
add_action('init', array('SmartDealer', 'addRoutes'));

// add custom params
add_filter('query_vars', array('SmartDealer', 'addParams'));

// add custom routes
add_action('wp_loaded', array('SmartDealer', 'flush'));

// lock duplicated calls (filters)
add_action('wp_head', array('SmartDealer', 'ready'));

// register
register_activation_hook(SmartDealer::pathPlugin(), array('SmartDealer', 'enable'));
register_deactivation_hook(SmartDealer::pathPlugin(), array('SmartDealer', 'disable'));
