<?php
/**
 * The template body
 *
 * @package   Smart Dealer Wordpress Plugin
 * @author    Patrick Otto <patrick@smartdealership.com.br>
 * @version   2.0
 * @access    public
 * @copyright Smart Dealer(c), Mar 2017-2018
 * @see       http://www.smartdealer.com.br
 */
if (!($row = $api->getRow()) or ! (is_array($row)) or ! array_key_exists('modelo', $row)) {
    get_header();
    SmartDealer::show_404();
}

# - - - - - - - - - - - - - - -
# Register lead
# - - - - - - - - - - - - - - -

$api->addLead(array(
    'id',
    'nome',
    'telefone',
    'email',
    'mensagem',
    'cidade',
    'estado'), array(), $out);

# - - - - - - - - - - - - - - -
# Header and meta tag (SEO)
# - - - - - - - - - - - - - - -

$api->setMetaTags(implode(' > ', array(get_bloginfo('name'), $row['marca'], $row['modelo'])), $row['marca'] . ' ' . $row['modelo'], SmartDealer::prepareKeyWords(implode(' ', array($row['marca'], $row['modelo'], $row['opcionais']))));

get_header();

# - - - - - - - - - - - - - - -
# Dynamic data
# - - - - - - - - - - - - - - -

$img = explode('/', substr($row['url_imagem'], 0, -4));
$year = explode('/', $row['ano_fab_mod']);
$year = (\SmartDealer::prepareYear(current($year)) . '/' . \SmartDealer::prepareYear(next($year)));
$fuel = \SmartDealer::prepareFuel($row['combustivel']);
$color = ucwords(\SmartDealer::prepareString($row['cor'], true));
$price = \SmartDealer::preparePrice($row['preco'], true);
?>

<script type="text/javascript">

    stm_lang_code = (typeof stm_lang_code === 'string') ? stm_lang_code : 'pt_br';
    ajaxurl = (typeof ajaxurl === 'string') ? ajaxurl : '<?php echo SmartDealer::formAction() ?>';

    STMListings = (typeof STMListings === 'object') ? STMListings : {
        Filter: {prototype: {
                ajaxBefore: function () {},
                ajaxSuccess: function () {}
            }}};

</script>

<div class="stm-single-car-page sd-scope">
    <div class="container">
        <div class="vc_row wpb_row vc_row-fluid">
            <div class="stm-vc-single-car-content-left wpb_column vc_column_container vc_col-sm-12 vc_col-lg-9">
                <div class="vc_column-inner ">
                    <div class="wpb_wrapper">
                        <?php $title = \SmartDealer::prepareString($row['modelo'], true) ?>
                        <h2 class="title" page-meta="title" meta-data="<?php echo $title; ?>"><?php echo $title; ?></h2>
                        <div class="single-car-actions">
                            <ul class="list-unstyled clearfix">
                                <!--Stock num-->
                                <li>
                                    <div class="stock-num heading-font"><span>CÓD #</span><?php echo ($row['id']) ?></div>
                                </li>
                                <!--Schedule-->
                                <li>
                                    <a href="#" class="car-action-unit stm-schedule" data-toggle="modal" data-target="#test-drive">
                                        <i class="stm-icon-steering_wheel"></i>
                                        Agendar Best Drive                
                                    </a>
                                </li>
                                <!--COmpare-->
                                <!--li>
                                    <a
                                        href="#"
                                        class="car-action-unit add-to-compare"
                                        data-id="<?php echo $row['id'] ?>"
                                        data-action="add">
                                        <i class="stm-icon-add"></i>
                                        Add. p/ comparar                    
                                    </a>
                                </li-->
                                <!--PDF-->
                                <!--Share-->
                                <!--Print button-->
                                <li>
                                    <a href="javascript:window.print()" class="car-action-unit stm-car-print heading-font">
                                        <i class="fa fa-print"></i>
                                        Imprimir página
                                    </a>
                                </li>
                                <!--Certified Logo 1-->
                                <!--Certified Logo 2-->
                            </ul>
                        </div>
						<div style="clear:both;width:100%;margin:0;height:0px;"></div>
						<div class="stm-car-carousels">
                            <!--New badge with videos-->
                            <div class="stm-big-car-gallery">
                                <?php $tot = ($row['tot_imagem'] >= 1) ? range(1, $row['tot_imagem']) : array(); ?>
                                <?php if ($tot): ?>
                                    <?php foreach ($tot as $i): ?>
                                        <div class="stm-single-image" data-id="big-image-<?php echo $i; ?>">
                                            <a href="<?php echo SmartDealer::img($row['id'], $img[0], $img[1], 1024, $i) ?>" class="stm_fancybox" rel="stm-car-gallery">
                                                <img width="800" height="auto" src="<?php echo SmartDealer::img($row['id'], $img[0], $img[1], 800, $i) ?>" class="img-responsive wp-post-image" alt="<?php echo $title ?>" />				
                                            </a>
                                        </div>
                                    <?php endforeach ?>
                                <?php endif; ?>
                            </div>
                            <div class="stm-thumbs-car-gallery">
                                <?php if ($tot): ?>
                                    <?php foreach ($tot as $i): ?>
                                        <div class="stm-single-image" id="big-image-<?php echo $i; ?>">
                                            <img width="300" height="auto" src="<?php echo SmartDealer::img($row['id'], $img[0], $img[1], 300, $i) ?>" class="img-responsive wp-post-image" alt="<?php echo $title ?>"/>				
                                        </div>
                                    <?php endforeach ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <!--Enable carousel-->
                        <script type="text/javascript">
                            jQuery(document).ready(function ($) {
                                var big = $('.stm-big-car-gallery');
                                var small = $('.stm-thumbs-car-gallery');
                                var flag = false;
                                var duration = 800;

                                var owlRtl = false;
                                if ($('body').hasClass('rtl')) {
                                    owlRtl = true;
                                }

                                big
                                        .owlCarousel({
                                            rtl: owlRtl,
                                            items: 1,
                                            smartSpeed: 800,
                                            dots: false,
                                            nav: false,
                                            margin: 0,
                                            autoplay: false,
                                            loop: false,
                                            responsiveRefreshRate: 1000
                                        })
                                        .on('changed.owl.carousel', function (e) {
                                            $('.stm-thumbs-car-gallery .owl-item').removeClass('current');
                                            $('.stm-thumbs-car-gallery .owl-item').eq(e.item.index).addClass('current');
                                            if (!flag) {
                                                flag = true;
                                                small.trigger('to.owl.carousel', [e.item.index, duration, true]);
                                                flag = false;
                                            }
                                        });

                                small
                                        .owlCarousel({
                                            rtl: owlRtl,
                                            items: 5,
                                            smartSpeed: 800,
                                            dots: false,
                                            margin: 22,
                                            autoplay: false,
                                            nav: true,
                                            loop: false,
                                            navText: [],
                                            responsiveRefreshRate: 1000,
                                            responsive: {
                                                0: {
                                                    items: 2
                                                },
                                                500: {
                                                    items: 4
                                                },
                                                768: {
                                                    items: 5
                                                },
                                                1000: {
                                                    items: 5
                                                }
                                            }
                                        })
                                        .on('click', '.owl-item', function (event) {
                                            big.trigger('to.owl.carousel', [$(this).index(), 400, true]);
                                        })
                                        .on('changed.owl.carousel', function (e) {
                                            if (!flag) {
                                                flag = true;
                                                big.trigger('to.owl.carousel', [e.item.index, duration, true]);
                                                flag = false;
                                            }
                                        });

                                if ($('.stm-thumbs-car-gallery .stm-single-image').length < 6) {
                                    $('.stm-single-car-page .owl-controls').hide();
                                    $('.stm-thumbs-car-gallery').css({'margin-top': '22px'});
                                }
                            });
                        </script>
                        <div class="vc_tta-container" data-vc-action="collapseAll">
                            <div class="vc_general vc_tta vc_tta-accordion vc_tta-o-shape-group vc_tta-o-no-fill vc_tta-o-all-clickable">
                                <div class="vc_tta-panels-container">
                                    <div class="vc_tta-panels">
                                        <div class="vc_tta-panel vc_active" id="1535736303477-f6c50be4-5579" data-vc-content=".vc_tta-panel-body">
                                            <div class="vc_tta-panel-heading">
                                                <h4 class="vc_tta-panel-title"><a href="#1535736303477-f6c50be4-5579" data-vc-accordion data-vc-container=".vc_tta-container"><span class="vc_tta-title-text">Lista dos itens de série</span><i class="vc_tta-controls-icon vc_tta-controls-icon-plus"></i></a></h4>
                                            </div>
                                            <div class="vc_tta-panel-body">
                                                <div class="">
                                                    <div class="stm-single-listing-car-features">
                                                        <div class="lists-inline">
                                                            <?php $opt = array_filter((array) (explode(';', trim($row['opcionais'], '; ')))) ?>
                                                            <?php if ($opt) : ?>
                                                                <ul class="list-style-3 " style="font-size: 13px;">
                                                                    <?php foreach ($opt as $op): ?> 
                                                                        <li><?php echo ucwords(SmartDealer::prepareOptString($op)) ?></li>
                                                                    <?php endforeach; ?>
                                                                </ul>  
                                                            <?php else: ?>
                                                                <span>Não informados</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="stm-vc-single-car-sidebar-right wpb_column vc_column_container vc_col-sm-12 vc_col-lg-3">
                <div class="vc_column-inner ">
                    <div class="wpb_wrapper">
                        <div class="single-car-prices">
                            <div class="single-regular-price text-center">
                                <?php if (intval($row['preco_promocional'])): ?>
                                    <span class="h3" style="font-size: 20px;"><strike><?php echo SmartDealer::preparePrice($row['preco'], 1, 0) ?></strike></span>
                                    <div class="clear clearfix" style="margin-bottom: 5px;"></div>
                                    <span class="h3"><?php echo SmartDealer::preparePrice($row['preco_promocional'], 1, 0) ?></span>
                                <?php else: ?>
                                    <span class="h3"><?php echo SmartDealer::preparePrice($row['preco'], 1, 0) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="stm-car_dealer-buttons heading-font">
                            <!--a href="#trade-in" data-toggle="modal" data-target="#trade-in">
                                Troca com troco	
                                <i class="stm-moto-icon-trade"></i>
                            </a>
                            <a href="#trade-offer" data-toggle="modal" data-target="#trade-offer">
                                Faça um preço de oferta	
                                <i class="stm-moto-icon-cash"></i>
                            </a-->
                        </div>
                        <div class="single-car-data">
                            <table>
                                <tr>
                                    <td class="t-label">Condição</td>
                                    <td class="t-value h6">Novos</td>
                                </tr>
                                <tr>
                                    <td class="t-label">Modelo</td>
                                    <td class="t-value h6"><?php echo strtok($row['modelo'], ' '); ?></td>
                                </tr>
                                <tr>
                                    <td class="t-label">Km</td>
                                    <td class="t-value h6">0</td>
                                </tr>
                                <tr>
                                    <td class="t-label">Combustível</td>
                                    <td class="t-value h6"><?php echo SmartDealer::prepareName($row['combustivel'], false) ?></td>
                                </tr>
                                <tr>
                                    <td class="t-label">Motor</td>
                                    <td class="t-value h6"><?php echo SmartDealer::prepareName($row['motor'], false) ?></td>
                                </tr>
                                <tr>
                                    <td class="t-label">Ano</td>
                                    <td class="t-value h6">
                                        <?php $year = explode('/', $row['ano_fab_mod']); ?>
                                        <?php echo SmartDealer::prepareYear(next($year)); ?>   
                                    </td>
                                </tr>
                                <tr>
                                    <td class="t-label">Transmissão</td>
                                    <td class="t-value h6"><?php echo SmartDealer::prepareString($row['transmissao'], false) ?></td>
                                </tr>
                                <tr>
                                    <td class="t-label">Portas</td>
                                    <td class="t-value h6"><?php echo ($row['portas']) ?></td>
                                </tr>
                                <tr>
                                    <td class="t-label">Cor</td>
                                    <td class="t-value h6"><?php echo SmartDealer::prepareName($row['cor']) ?></td>
                                </tr>
                                <!--VIN NUMBER-->
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="vc_row wpb_row vc_row-fluid vc_custom_1535736497514">
            <div class="wpb_column vc_column_container vc_col-sm-12">
                <div class="vc_column-inner ">
                    <div class="wpb_wrapper">
                        <div class="vc_row wpb_row vc_inner vc_row-fluid">
                            <div class="wpb_column vc_column_container vc_col-sm-12">
                                <div class="vc_column-inner ">
                                    <div class="wpb_wrapper">
                                        <div
                                            class="icon-box vc_custom_1535744701418 icon_box_39626 stm-layout-box-car_dealer"
                                            style="color:#232628">
                                            <div class="boat-line"></div>
                                            <div
                                                class="icon vc_custom_1535744701416 boat-third-color"
                                                style="font-size:27px;color:#6c98e1; ">
                                                <i class="fa fa-paper-plane"></i>
                                            </div>
                                            <div class="icon-text">
                                                <h4 class="title heading-font" style="color:#232628">
                                                    Ficou interessado? Cadastre-se e receba uma ligação.				
                                                </h4>
                                            </div>
                                        </div>
                                        <style>
                                            .icon_box_39626:after,
                                            .icon_box_39626:before {
                                                background-color: #ffffff;
                                            }
                                            .icon_box_39626 .icon-box-bottom-triangle {
                                                border-right-color:rgba(255,255,255,0.9);
                                            }
                                            .icon_box_39626:hover .icon-box-bottom-triangle {
                                                border-right-color:rgba(255,255,255,1);
                                            }
                                            .icon-box .icon-text .content a {
                                                color: #232628;
                                            }
                                        </style>
                                        <div role="form" class="wpcf7" id="wpcf7-f5283-p5657-o1" lang="en-US" dir="ltr">
                                            <div class="screen-reader-response"></div>
                                            <form action="<?php echo SmartDealer::formAction(); ?>#wpcf7-f5283-p5657-o1" method="post" class="wpcf7-form mailchimp-ext-0.4.50" novalidate="novalidate">
                                                <div style="display: none;">
                                                    <input type="hidden" name="_wpcf7" value="5283">
                                                    <input type="hidden" name="_wpcf7_version" value="5.0.4">
                                                    <input type="hidden" name="_wpcf7_locale" value="en_US">
                                                    <input type="hidden" name="_wpcf7_unit_tag" value="wpcf7-f5283-p5657-o1">
                                                    <input type="hidden" name="_wpcf7_container_post" value="5657">
                                                </div>
                                                <?php if ($out): ?>
                                                    <div class="wpcf7-response-output wpcf7-display-none" style="margin-bottom: 10px;">Sucesso! Um de nossos vendedores entrará em contato.</div>
                                                <?php elseif ($out === false): ?>
                                                    <div class="wpcf7-response-output wpcf7-display-none" style="margin-bottom: 10px;">Ops! Ocorreu um erro ao enviar as informações.</div>
                                                <?php endif; ?>
                                                <p style="display: none !important"><span class="wpcf7-form-control-wrap referer-page"><input type="hidden" name="referer-page" value="?taxonomy=condition&term=novos" class="wpcf7-form-control wpcf7-text referer-page" aria-invalid="false"></span></p>
                                                <!-- Chimpmail extension by Renzo Johnson -->
                                                <div class="row">
                                                    <div class="col-md-12 col-sm-12">
                                                        <div class="form-group">
                                                            <div class="form-label">Nome:</div>
                                                            <p>         <span class="wpcf7-form-control-wrap your-name"><input type="text" name="your-name" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" /></span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-sm-6">
                                                        <div class="form-group">
                                                            <div class="form-label">Seu melhor e-mail:</div>
                                                            <p>         <span class="wpcf7-form-control-wrap your-email"><input type="email" name="your-email" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-email wpcf7-validates-as-required wpcf7-validates-as-email" aria-required="true" aria-invalid="false" /></span>
                                                            </p>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="form-label">Telefone / Whatsapp:</div>
                                                            <p>         <span class="wpcf7-form-control-wrap your-tel"><input type="tel" name="your-tel" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-tel wpcf7-validates-as-required wpcf7-validates-as-tel" aria-required="true" aria-invalid="false" /></span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-sm-6">
                                                        <div class="form-group">
                                                            <div class="form-label">Cidade:</div>
                                                            <p>         <span class="wpcf7-form-control-wrap Cidade"><input type="text" name="Cidade" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" /></span>
                                                            </p>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="form-label">CPF ou CNPJ:</div>
                                                            <p>         <span class="wpcf7-form-control-wrap cpf_cnpj"><input type="text" name="cpf_cnpj" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" /></span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 col-sm-12">
                                                        <input type="submit" value="Enviar" class="wpcf7-form-control wpcf7-submit" />
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>


<!-- page styles -->
<link href="<?php echo SmartDealer::url('css/font-awesome.min.css'); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo SmartDealer::url('css/owl.carousel.css'); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo SmartDealer::url('css/owl.theme.css'); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo SmartDealer::url('css/ekko-lightbox.min.css'); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo SmartDealer::url('css/custom/itauto.css'); ?>" rel="stylesheet" type="text/css"/>

<!-- page scripts -->
<script type="text/javascript" src="<?php echo SmartDealer::url('js/owl.carousel.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo SmartDealer::url('js/ekko-lightbox.min.js'); ?>"></script>

<!-- footer -->
<?php get_footer(); ?>