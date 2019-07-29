<?php

/*
  Plugin Name: Smart Dealer
  Plugin URI: http://smartdealership.com.br/api.html
  Description: Plugin de integração com o Smart Dealer (compatível com wordpress 3 ou superior) - 2017-2019
  Version: 2.0.5
  Author: Patrick Otto <patrick@smartdealership.com.br>, Jean Carlos dos Santos <jean@smartdealership.com.br>
  Author URI: http://smartdealer.com.br
 */

class SmartDealer extends stdClass
{

    const CONFIG_TITLE = 'Integração com o Smart Dealer';
    const MENU_NAME = 'Smart Dealer';
    const NUSOAP_PATH = 'includes/nusoap/nusoap.php';
    const NUSOAP_CACHE_PATH = 'includes/nusoap/class.wsdlcache.php';
    const IMG_PLUGIN_URL = 'getimage';
    const IMG_EXT = '.jpg';
    const PAGER_TOTAL_ROWS = 6;
    const PAGER_TOTAL_PAGES = 10;
    const WS_DATA_CACHE_TIME = 86400;
    const WS_CACHE_STATUS = true;
    const DEBUG_MODE = false;

    /**
     * Static 
     */
    private static $slug, $link_modal, $img_owner, $template;

    /**
     * Protected 
     */
    protected $ws, $ws_path, $ws_method, $ws_modal, $instance, $data = array(), $log = array(), $meta = array(), $in_use = array();

    /**
     * WS request schema 
     */
    public $schema = array(
        'novos' => array(
            'include' => 'novos.php',
            'method' => 'CarrosNovos',
            'modal' => 'novos',
            'uri' => 'novo'
        ),
        'usados' => array(
            'include' => 'usados.php',
            'method' => 'CarrosUsados',
            'modal' => 'usados',
            'uri' => 'usado'
        ),
        'estoque_futuro' => array(
            'include' => 'estoque-futuro.php',
            'method' => 'CarrosCorsia',
            'modal' => 'corsia',
            'uri' => 'corsia'
        ),
        'novo' => array(
            'include' => 'novo.php',
            'method' => 'CarroNovo',
            'modal' => 'novo',
            'uri' => false
        ),
        'usado' => array(
            'include' => 'usado.php',
            'method' => 'CarroUsado',
            'modal' => 'usado',
            'uri' => false
        ),
        'corsia' => array(
            'include' => 'corsia.php',
            'method' => 'CarroCorsia',
            'modal' => 'corsia',
            'uri' => false
        ),
        'ofertas' => array(
            'include' => 'ofertas.php',
            'method' => 'CarrosDestaque',
            'modal' => '',
            'uri' => false
        ),
        'smartdealer_data' => array(
            'include' => 'data.php',
            'method' => '',
            'modal' => '',
            'uri' => false
        )
    );

    public function __construct()
    {

        // set path
        $this->ws_path = (stristr(get_option('smartdealer_instancia'), '://')) ? get_option('smartdealer_instancia') : 'https://' . str_replace(array('.smartdealer.app', '.smartdealer.com.br'), '', get_option('smartdealer_instancia')) . '.smartdealer.app/webservice/core.php?wsdl';

        // debug mode (for local tests)
        if (self::DEBUG_MODE) {
            $this->ws_path = 'http://localhost/smartdealer/webservice/core.php?wsdl';
        }

        // set owner
        self::$img_owner = (($a = get_option('smartdealer_instancia')) && stristr($a, '_')) ? end((explode('_', $a))) : $a;

        // set template
        self::$template = (($tpl = get_option('smartdealer_template'))) ? $tpl : 'default';

        // save var on session (share with subplugins)
        $this->save();

        // set instance
        $this->instance = $this;

        // set meta params
        $this->meta = (!empty($_SESSION['smartdealer_meta'])) ? $_SESSION['smartdealer_meta'] : array();
    }

    private function save()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // set data
        $_SESSION['smartdealer_instancia'] = get_option('smartdealer_instancia');
    }

    public function addRoutes()
    {
        global $wp_rewrite;

        $wp_rewrite->add_external_rule('^novo/([^/]+)/([^/]+)/?', 'wp-content/plugins/smartdealer/' . self::getTemplate() . '/novo.php?id=$matches[2]');
        $wp_rewrite->add_external_rule('^usado/([^/]+)/([^/]+)/?', 'wp-content/plugins/smartdealer/' . self::getTemplate() . '/usado.php?id=$matches[2]');

        $wp_rewrite->flush_rules(true);
    }

    public function flush()
    {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }

    public function addParams($query_vars)
    {
        $query_vars[] = 'veiculo';
        return $query_vars;
    }

    public function enable()
    {
        update_option('smartdealer_instancia', '');
        update_option('smartdealer_usuario', '');
        update_option('smartdealer_senha', '');
    }

    public function disable()
    {
        delete_option('smartdealer_instancia');
        delete_option('smartdealer_usuario');
        delete_option('smartdealer_senha');
    }

    public function createMenu()
    {
        add_menu_page(self::CONFIG_TITLE, self::MENU_NAME, 10, 'smartdealer/smartdealer-config.php', null, plugins_url() . '/smartdealer/assets/img/favicon.ico');
    }

    static function the_slug()
    {
        self::$slug = basename(get_permalink());
        do_action('before_slug', self::$slug);
        self::$slug = apply_filters('slug_filter', self::$slug);
        do_action('after_slug', self::$slug);
        return self::$slug;
    }

    static function the_id()
    {
        $a = urldecode(self::uri(-1));
        $c = base64_decode($a);
        return ($a === base64_encode($c)) ? $c : null;
    }

    static function encodeData($a)
    {
        return base64_encode($a);
    }

    static function decodeData($a)
    {
        return base64_decode($a);
    }

    static function link($a, $b = null)
    {
        $b = preg_replace('/\s+/', '-', self::prepareString($b));
        return site_url() . '/' . self::$link_modal . (($b) ? '/' . $b : '') . '/' . urlencode(base64_encode($a));
    }

    public function isIncluded($fileName)
    {
        return in_array(__DIR__ . DS . '..' . DS . 'template' . DS . self::getTemplate() . DS . $fileName, get_included_files());
    }

    public static function formAction()
    {
        return current(explode('?', getenv('REQUEST_URI')));
    }

    public function pathPlugin()
    {
        return substr(strrchr(dirname(__FILE__), DIRECTORY_SEPARATOR), 1) . DIRECTORY_SEPARATOR . basename(__FILE__);
    }

    public function realPathPlugin($a = null)
    {
        return dirname(__FILE__) . DS . '..' . DS . (($a) ? $a : '');
    }

    public function uri($i = null)
    {
        $uri = trim(str_replace(preg_replace('/https?:\/\//i', '', get_site_url()), '', getenv('HTTP_HOST') . getenv('REQUEST_URI')), '/ ');
        $uri_params = array_filter(array_map(function ($a) {
            return strtok($a, '?');
        }, ((array) (explode(US, $uri)))));
        return (!empty($uri_params[$i])) ? $uri_params[$i] : (($i == -1 && $uri_params && is_array($uri_params)) ? end($uri_params) : $uri);
    }

    public static function url($a)
    {
        return plugins_url() . US . 'smartdealer' . US . 'assets' . US . trim($a, '/ ');
    }

    public static function urlPlugin($a = null)
    {
        return plugins_url() . US . 'smartdealer' . US . (($a) ? trim($a, '/ ') : '');
    }

    public static function urlBase($a = null)
    {
        return trim(\get_site_url(), '/ ') . US . (($a) ? $a . US : '');
    }

    public static function formGet($a, $b = null)
    {
        return ($c = filter_input(INPUT_GET, $a, FILTER_SANITIZE_STRING)) ? $c : $b;
    }

    public function connect()
    {

        // fix WSDL endopoint
        $this->ws_path = strtolower($this->ws_path);

        // add plugin
        require_once $this->realPathPlugin(self::NUSOAP_PATH);
        require_once $this->realPathPlugin(self::NUSOAP_CACHE_PATH);

        //cache vars
        $cache_dir = $this->realPathPlugin('cache');
        $wsdl_cache_lifetime = 600;

        // construct cache
        $wsdlCache = new nusoap_wsdlcache($cache_dir, $wsdl_cache_lifetime);

        // try to get cache
        $wsdl = $wsdlCache->get($this->ws_path);

        // cache file not found, need to create new one
        if (empty($wsdl)) {
            $wsdl = new wsdl();
            $wsdl->setCredentials(get_option('smartdealer_usuario'), get_option('smartdealer_senha'));
            $wsdl->fetchWSDL($this->ws_path);
            $wsdlCache->put($wsdl);
        }

        // instance nusoap
        $this->ws = new \nusoap_client($wsdl, true);

        // set credentials
        $this->ws->setCredentials(get_option('smartdealer_usuario'), get_option('smartdealer_senha'));

        // debug (log error)
        $this->logError($this->ws->getError());

        // boot status
        return (bool) (!$this->logError());
    }

    public function logError($a = null)
    {
        if ($a) {
            $this->log[] = $a;
        } else {
            return $this->log;
        }
    }

    static public function show_404()
    {
        status_header(404);
        include(get_query_template('404'));
        exit(0);
    }

    public function getData()
    {

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
            'query' => self::formGet('busca_smart', '')
        ));

        $prm['parametrospage'] = array_filter($prm['parametrospage']);

        // call ws
        $this->data = $this->call($this->ws_method, $prm);

        // return
        return $this->data;
    }

    public function getRow()
    {

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
        $this->data = $this->call($this->ws_method, $prm);

        // return
        return $this->data;
    }

    public function getMarks()
    {

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
        $this->marks = $this->call('GetMarcas', $prm);

        // return
        return ($this->marks && !$this->ws->getError()) ? $this->marks : array();
    }

    public function getModels()
    {

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
        $this->models = $this->call('GetModelos', $prm);

        // return
        return ($this->models && !$this->ws->getError()) ? $this->models : array();
    }

    public function getFamilies()
    {

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

        if (self::formGet('marca', '')) {
            $prm['parametrosbusca']['texto'] = self::formGet('marca');
        }

        // call ws
        $this->families = $this->call('GetFamilias', $prm);

        // return
        return ($this->families && !$this->ws->getError()) ? $this->families : array();
    }

    public function getColors()
    {

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
        $this->colors = $this->call('GetCores', $prm);

        // return
        return ($this->colors && !$this->ws->getError()) ? $this->colors : array();
    }

    public function getFuels()
    {

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
        $this->fuels = $this->call('GetCombustiveis', $prm);

        // return
        return ($this->fuels && !$this->ws->getError()) ? $this->fuels : array();
    }

    public function getGears()
    {

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
        $this->colors = $this->call('GetCambios', $prm);

        // return
        return ($this->colors && !$this->ws->getError()) ? $this->colors : array();
    }

    public function getTotals()
    {

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
        $this->totals = $this->call('GetTotais', $prm);

        // return
        return ($this->totals && !$this->ws->getError()) ? $this->totals : array();
    }

    public function getOffers($type = null, $limit = null, $filter = false)
    {

        self::$link_modal = $type;

        // se params
        $prm = array('parametrospage' => array(
            'modal' => ($type) ? $type : $this->ws_modal
        ));

        $prm['parametrospage']['reg_max'] = ($limit) ? $limit : self::PAGER_TOTAL_PAGES;

        if (self::formGet('estoque', '')) {
            $prm['parametrospage']['filial'] = self::formGet('filial');
        }

        if ($filter) {
            $prm['parametrospage']['filtra_destaques'] = true;
        } else {
            $prm['parametrospage']['ignora_destaques'] = true;
        }

        // call ws
        $this->ofertas = $this->call('CarrosDestaque', $prm);

        // return
        return ($this->ofertas && !$this->ws->getError()) ? $this->ofertas : array();
    }

    public function addLead($keys, $ignore = array(), &$ret)
    {

        // valid method
        if (stristr($_SERVER['REQUEST_METHOD'], 'POST')) {

            // force remove unsed
            $keys = array_diff_key($keys, array_flip((array) $ignore));

            // filter input
            $in = ($in = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING)) ? array_map(function ($a) {
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
            $this->response = $this->call('EuQuero', $prm);

            // status
            $ret = (bool) ($this->response === 'sucesso' && !$this->ws->getError());
        }
    }

    static public function prepareName($str, $filter = true)
    {

        $pat = array('/\&[A-Z]+\;/i', '/[^A-Za-z0-9\-\. ]\s/');

        // especial filter (remove catalog)
        if ($filter) {
            $pat[-1] = '/^\w(\W|\s)/';
        }

        // prepare string
        return trim(ucwords(preg_replace($pat, ' ', html_entity_decode($str))));
    }

    static public function prepareString($str, $lower = true)
    {

        // check encode
        $str = self::prepareCharset($str);

        // crate patterns
        $patterns = array(
            'a' => '/À|Â|Ä|Å|à|â|ä|å/m',
            'á' => '/á|Á/m',
            'ã' => '/ã|Ã/m',
            'ç' => '/Ç|ç/m',
            'e' => '/È|É|Ê|Ë|è|é|ê|ë/m',
            'i' => '/Ì|Í|Î|Ï|ì|í|î|ï/m',
            'n' => '/Ñ|ñ/m',
            'o' => '/Ò|Ó|Ô|Õ|Ö|ò|ó|ô|õ|ö/m',
            'u' => '/Ù|Ú|Û|Ü|ù|ú|û|ü/m',
            'y' => '/Ý|ý|ÿ/m',
            '' => '/[\"\'\/]/m',
            '' => '/\//m',
            '' => '/\r|\t|\n/',
            ' ' => '/([^A-Za-z0-9_\.Áá ])+/m',
            ' ' => '/\s\s?\s?/m',
            '' => '/\(|\)|\[|\]/m'
        );

        // find and replace special and invalid chars
        $str = preg_replace($patterns, array_keys($patterns), $str);

        // convert to standard format
        $str = html_entity_decode(str_replace('/', ' ', stripslashes($str)));
        $str = ($lower) ? ucwords(mb_strtolower($str)) : mb_strtoupper($str);

        // return
        return trim($str);
    }

    /**
     * String repair charset to UTF-8
     * @method  prepareCharset
     * @param   string $str text to tratament
     * @param   type $str
     * @access  public
     * @copyright (c) 2016, System Support Tools
     */
    static public function prepareCharset($str)
    {

        $o = $str;

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

    static public function prepareFuel($a)
    {
        return preg_match('/(gas([a-z]+)?|alc([a-z]+)?|etan([a-z]+)?).*(gas([a-z]+)?|alc([a-z]+)?|etan([a-z]+)?)/i', $a) ? 'Flex' : ucfirst(strtolower($a));
    }

    public static function array_column($array, $column, $key = null, $pkey = false)
    {
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
        if (($keyArray && $key and (!is_array($key) && !is_object($key)) or $pkey) && count($keyArray) === count($newArray))
            $newArray = array_combine((array) $keyArray, (array) $newArray);

        // return
        return $newArray;
    }

    public static function img($a, $m, $c, $w = 500, $s = 1, $u = 0, $prm = '')
    {

        // detect and cache owner
        self::$img_owner = (self::$img_owner) ? self::$img_owner : ((($r = parse_url(get_option('sds_instancia'))) && !empty($r['host'])) ? strtok($r['host'], '.') : ((isset($r['path'])) ? $r['path'] : $r));

        // return
        return self::urlPlugin('includes/getimage' . US . (($u) ? $a . US . self::$img_owner . US . $w . US . $s : urlencode($m) . US . urlencode($c) . US . self::$img_owner . US . $w . US . $s) . self::IMG_EXT . (($prm) ? '?' . trim(((is_string($prm)) ? $prm : http_build_query($prm)), '?\//& ') : ''));
    }

    static public function prepareYear($year = 0, $used = true)
    {

        // year tratament and validation
        $year = ($year === 00) ? substr(date('Y'), 0, 1) . '000' : ((empty($year)) ? date('Y') : $year);

        // return yeaer wicth four digits
        return (int) (strlen($year) <= 2) ? (($year > (date('y') + 2) and ($year <= 99) and $used) ? $year + 1900 : $year + ((int) substr(date('Y'), 0, 2) . "00")) : $year;
    }

    static public function preparePrice($a, $b = 0, $c = 2)
    {
        // prepare float number 
        return (((int) str_replace(',', '', $a)) < 10) ? 'Sob Consulta' : ((($b) ? 'R$ ' : '') . number_format($a, $c, ',', '.'));
    }

    public function getPager($data)
    {

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
        if ($page != 1) :
            $pager[] = array(
                'link' => $link . 'pp=1',
                'text' => 'Primeira',
                'atual' => '0',
                'class' => 'pager-first'
            );
            $pager[] = array(
                'link' => $link . 'pp=' . ($page - 1),
                'text' => ' &laquo;',
                'atual' => '0',
                'class' => 'pager-opt'
            );
        endif;

        // add middle pages
        for ($k = 1; $k <= $pages; ++$k) :

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
                    'atual' => $active,
                    'class' => 'pager-active'
                );
        endfor;

        // add end shotcuts
        if ($pages > $page) :
            $pager[] = array(
                'link' => $link . 'pp=' . ($page + 1),
                'text' => '&raquo;',
                'atual' => '0',
                'class' => 'pager-middle'
            );

            $pager[] = array(
                'link' => $link . 'pp=' . $pages,
                'text' => 'Última',
                'atual' => '0',
                'class' => 'pager-last'
            );
        endif;

        // return
        return $pager;
    }

    public static function getURL()
    {
        return implode('', array('http://', $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']));
    }

    static public function prepareText($str, $strip = 0)
    {

        $str = self::prepareCharset($str);

        // return
        return trim(html_entity_decode($str));
    }

    public static function prepareOptString($str)
    {

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

    static public function getSocial()
    {
        return get_option('sds_instancia');
    }

    public function setModal($a)
    {
        if (($b = in_array($a, array('novos', 'usados', 'novo', 'usado')))) {
            $this->ws_modal = $a;

            // set child URI
            self::$link_modal = $a;
        }

        // return
        return $b;
    }

    // the compile
    function compile($a, $subpages = false)
    {

        global $wpdb;
        $api = $this;
  
        if (!is_admin() && $this->connect()) {
            foreach ($this->schema as $k => $b) {

                if (!in_array($k, $this->in_use) && ($a === $k) && !$this->isIncluded($b['include']) && (($subpages && $b['uri'] === false) or (!$subpages && !empty($b['uri'])))) {

                    // hide admin bar
                    show_admin_bar(false);

                    // create scope
                    ob_start(null, null);

                    // save use
                    $this->in_use[] = $k;

                    // set modal (type)
                    $this->ws_modal = $b['modal'];

                    // set method (ws route)
                    $this->ws_method = $b['method'];

                    // set child URI
                    self::$link_modal = $b['uri'];

                    // include tpl
                    if (file_exists($this->realPathPlugin('template' . DS . self::getTemplate() . DS . $b['include']))) {
                        include_once($this->realPathPlugin('template' . DS . self::getTemplate() . DS . $b['include']));
                    } else {
                        echo 'O arquivo ' . $b['include'] . ' não existe na pasta do template.';
                    }

                    // collect data
                    $c = ob_get_contents();

                    // reset
                    ob_clean();

                    // kill
                    break;
                }
            }
            
            // return
            return (!empty($c)) ? $c : '';
        } else {
            return 'Não foi possível conectar ao webservice';
        }
    }

    private function getCache($key)
    {

        // cache vars
        $cache_dir = $this->realPathPlugin('cache');

        // set file
        $a = $cache_dir . DS . $key . '.lock';

        if (file_exists($a) && (time() - filectime($a)) <= self::WS_DATA_CACHE_TIME) {
            return unserialize(file_get_contents($a));
        }
    }

    private function setCache($key, array $data)
    {

        // cache vars
        $cache_dir = $this->realPathPlugin('cache');

        // create file
        $a = fopen($cache_dir . DS . $key . '.lock', "w+") or die('Unable to open file!');

        // save
        fwrite($a, serialize($data));

        // close file
        fclose($a);
    }

    public function resetCache()
    {

        $r = 0;

        // cache vars
        $cache_dir = $this->realPathPlugin('cache');

        // read files
        $files = glob(trim($cache_dir, DS . ' ') . DS . '*');

        // delete
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $r++;
            }
        }

        $m = 0;

        // cache vars
        $cache_dir = $this->realPathPlugin('includes' . DS . 'getimage' . DS . 'cache');

        // read files
        $files = glob(trim($cache_dir, DS . ' ') . DS . '*');

        // delete
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $m++;
            }
        }

        return true;
    }

    private function call($a, array $b = array())
    {

        // set request signature key
        $key = $a . '.' . sha1(json_encode((array) ($b)));

        // call ws
        $r = (self::WS_CACHE_STATUS && ($c = $this->getCache($key))) ? $c : $this->ws->call($a, $b);

        // set cache (response from WS)
        if (!$c && is_array($r) && self::WS_CACHE_STATUS) {
            $this->setCache($key, $r);
        }

        // debug (log error)
        $this->logError($this->ws->getError());

        ob_end_clean();

        // debug mode
        if ($this->ws->getError() && self::DEBUG_MODE) {
            ob_clean();
            var_dump($a, $b, $r, $this->logError());
            echo "<pre>";
            echo $this->debug();
            die;
        }

        // return
        return ($r) ? $r : array();
    }

    public function debug()
    {
        return $this->ws->response;
    }

    public function templates()
    {

        // default
        $a = array();

        // cache vars
        $cache_dir = $this->realPathPlugin('template');

        // read files
        $files = array_filter(glob(trim($cache_dir, DS . ' ') . DS . '*'), 'is_dir');

        // delete
        foreach ($files as $file) {
            $key = preg_replace('/.*[\/\\\]([a-zA-Z0-9-_]+)$/', '$1', $file);
            $a[$key] = ucfirst($key);
        }


        // return
        return $a;
    }

    public function getLinkFlag($a)
    {

        global $wpdb;

        // set query
        $b = $wpdb->get_row('SELECT post_name AS slug FROM ' . $wpdb->prefix . 'posts where post_content like "%[' . $a . ']%" and post_type IN(\'post\',\'page\') and post_status = \'publish\' LIMIT 1');

        // return
        return (!empty($b->slug)) ? site_url($b->slug) : '#';
    }

    public function setMetaTags($a, $b = '', $c = '')
    {
        $_SESSION['smartdealer_meta'] = array(
            '<title>' . $a . '</title>',
            '<meta name="keywords" content="' . $c . '">',
            '<meta name="description" content="' . $b . '">'
        );
    }

    public function applyMetaTags()
    {
        echo implode("\n", $this->meta);
    }

    public static function getTemplate()
    {
        return self::$template;
    }

    static public function prepareKeyWords($str)
    {

        if (is_array($str)) {
            $str = implode(' ', $str);
        }

        // rules
        $pattern = array(
            '@\b(de|da|das|dos|do|a|ante|apos|ate|com|contra|desde|em|entre|para|per|perante|por|sem|sob|sobre|tras)\b@i' => '',
            '/[^\w\d\s]+/im' => ' ',
            '/\s+/' => ' '
        );

        // prepare string
        return implode(',', array_filter(explode(' ', preg_replace(array_keys($pattern), array_values($pattern), self::prepareString($str)))));
    }
}
