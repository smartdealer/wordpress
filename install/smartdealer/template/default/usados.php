<?php
/**
 * The template body
 *
 * @package   Smart Dealer Wordpress Plugin
 * @author    Patrick Otto <patrick@smartdealership.com.br>
 * @version   2.0.2
 * @access    public
 * @copyright Smart Dealer(c), 2017-2018
 * @see       http://smartdealer.com.br
 */
$totais = $this->getTotals();

$valid = (!empty($totais) && is_array($totais) && key_exists(0, $totais)) && key_exists('preco_min', $totais[0]);
$pr_min = (int) ($valid) ? min(\SmartDealer::array_column((array) $totais, 'preco_min')) : 0;
$pr_max = (int) ($valid) ? max(\SmartDealer::array_column((array) $totais, 'preco_max')) : 0;
$ye_min = (int) ($valid) ? min(\SmartDealer::array_column((array) $totais, 'ano_min')) : date('Y');
$ye_max = (int) ($valid) ? max(\SmartDealer::array_column((array) $totais, 'ano_max')) : date('Y');
?>

<script type="text/javascript">

    stm_lang_code = (typeof stm_lang_code === 'string') ? stm_lang_code : 'pt_br';
    ajaxurl = (typeof ajaxurl === 'string') ? ajaxurl : '<?php echo SmartDealer::formAction() ?>';
    
    STMListings = (typeof STMListings === 'object') ? STMListings : {
        Filter: {prototype: {
                ajaxBefore: function () {},
                ajaxSuccess: function () {}
            }}};

    jQuery(function () {
        jQuery('#page-form').delegate('select', 'change', function () {
            $('#page-form').submit();
        });
    });

</script>

<style type="text/css">

    .stm_breadcrumbs_unit.heading-font{
        display: none;
    }

    .pager-last a, .pager-first a{
        min-width: 35px !important;
        width: 100% !important;
    }

    .ui-slider .ui-slider-handle:after {
        background-color: #fff !important;
    }

    .ui-slider .ui-slider-handle{
        border-radius: 100% !important;
        background: #f0f2f5;
    }

    .ui-slider .ui-slider-handle:hover{
        cursor: pointer;
    }

    .range-label{
        text-transform: uppercase;
        color: #888;
        font-size: 10px;
        margin-bottom: 10px;
    }

    .range{
        margin-top: 15px;
        font-weight: 500;
        font-size: 14px;
        color: #232628;
    }

    .range.left{
        float: left;
    }

    .range.right{
        float: right;
    }

    .sidebar-action-units{
        margin-top: 10px;
    }

    .ui-slider-horizontal {
        height: 5px !important;
    }

</style>
<form id="page-form" action="<?php echo SmartDealer::formAction() ?>" name="busca" method="get">
    <div class="container">
        <div class="vc_row wpb_row vc_row-fluid vc_custom_1448609395747">
            <div class="wpb_column vc_column_container vc_col-sm-12">
                <div class="vc_column-inner ">
                    <div class="wpb_wrapper">
                        <div class="archive-listing-page">
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-3 col-sm-12 classic-filter-row sidebar-sm-mg-bt ">
                                        <div class="filter filter-sidebar ajax-filter">
                                            <div class="sidebar-entry-header">
                                                <i class="stm-icon-car_search"></i>
                                                <span class="h4">Opções de busca</span>
                                            </div>
                                            <div class="row row-pad-top-24">
                                                <div class="col-md-12 col-sm-6 stm-filter_condition" style="padding-bottom: 15px;">
                                                    <div class="input-group">
                                                        <input type="text" name="busca" class="form-control" placeholder="Busque.." value="<?php echo SmartDealer::formGet('busca'); ?>" aria-label="Busque..">
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-outline-secondary" type="submit" style="height: 38px;"><i class="fa fa-search"></i></button>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 col-sm-6 stm-filter_condition">
                                                    <div class="form-group">
                                                        <select name="marca" class="form-control select2-hidden-accessible" tabindex="-1" aria-hidden="true">
                                                            <option value="" selected="selected">Marca</option>
                                                            <?php $a = $this->getMarks(); ?>
                                                            <?php foreach ($a as $row): ?>
                                                                <option value="<?php echo $row['item'] ?>" <?php if (SmartDealer::formGet('marca') == $row['item']) echo "selected=selected" ?>><?php echo SmartDealer::prepareString($row['item'], false) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>				
                                                    </div>
                                                </div>
                                                <div class="col-md-12 col-sm-6 stm-filter_condition">
                                                    <div class="form-group">
                                                        <select name="modelo" class="form-control select2-hidden-accessible" tabindex="-1" aria-hidden="true">
                                                            <option value="" selected="selected">Modelo</option>
                                                            <?php $a = $this->getModels(); ?>
                                                            <?php foreach ($a as $row): ?>
                                                                <option value="<?php echo $row['item'] ?>" <?php if (SmartDealer::formGet('modelo') == $row['item']) echo "selected=selected" ?>><?php echo SmartDealer::prepareString($row['item'], false) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>				
                                                    </div>
                                                </div>
                                                <div class="col-md-12 col-sm-6 stm-filter_body">
                                                    <div class="form-group">
                                                        <select name="cor" class="form-control select2-hidden-accessible" tabindex="-1" aria-hidden="true">
                                                            <option value="" selected="selected">Cor</option>
                                                            <?php $a = $this->getColors(); ?>
                                                            <?php foreach ($a as $row): ?>
                                                                <option value="<?php echo $row['item'] ?>" <?php if (SmartDealer::formGet('cor') == $row['item']) echo "selected=selected" ?>><?php echo $row['item'] ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 col-sm-6 stm-filter_make">
                                                    <div class="form-group">
                                                        <select name="combustivel" class="form-control select2-hidden-accessible" tabindex="-1" aria-hidden="true">
                                                            <option value="" selected="selected">Combustível</option>
                                                            <?php $a = $this->getFuels(); ?>
                                                            <?php foreach ($a as $row): ?>
                                                                <option value="<?php echo $row['item'] ?>" <?php if (SmartDealer::formGet('combustivel') == $row['item']) echo "selected=selected" ?>><?php echo $row['item'] ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 col-sm-6 stm-filter_ca-year">
                                                    <div class="form-group">
                                                        <label class="range-label">Faixa de ano</label>
                                                        <div class="range-slider-gp">
                                                            <div id="range-ano"></div>
                                                            <div class="clearfix"></div>
                                                            <div class="range left" id="range-min-ano"></div>
                                                            <div class="range right" id="range-max-ano"></div>
                                                            <input id="ano-min" name="ano_min" type="hidden" value="<?php echo SmartDealer::formGet('ano_min', 0) ?>"/>
                                                            <input id="ano-max" name="ano_max" type="hidden" value="<?php echo SmartDealer::formGet('ano_max', 0) ?>"/>
                                                        </div>    
                                                    </div>
                                                </div>
                                                <div class="col-md-12 col-sm-6 stm-filter_ca-year">
                                                    <div class="form-group">
                                                        <label class="range-label">Faixa de preço</label>
                                                        <div class="range-slider-gp">
                                                            <div id="range-preco"></div>
                                                            <div class="clearfix"></div>
                                                            <div class="range left" id="range-min"></div>
                                                            <div class="range right" id="range-max"></div>
                                                            <input id="preco-min" name="preco_min" type="hidden" value="<?php echo SmartDealer::formGet('preco_min', 0) ?>"/>
                                                            <input id="preco-max" name="preco_max" type="hidden" value="<?php echo SmartDealer::formGet('preco_max', 0) ?>"/>
                                                        </div>       
                                                    </div>
                                                </div>
                                            </div>
                                            <!--View type-->
                                            <input type="hidden" id="stm_view_type" name="view_type" value="">
                                            <!--Filter links-->
                                            <input type="hidden" id="stm-filter-links-input" name="stm_filter_link" value="">
                                            <!--Popular-->
                                            <input type="hidden" name="popular" value="">
                                            <input type="hidden" name="s" value="">
                                            <input type="hidden" name="sort_order" value="">
                                            <!-- Smart Dealer -->

                                            <input type=hidden name="st_preco_min" value="<?php echo $pr_min ?>">
                                            <input type=hidden name="st_preco_max" value="<?php echo $pr_max ?>">
                                            <input type=hidden name="st_ano_min" value="<?php echo $ye_min ?>">
                                            <input type=hidden name="st_ano_max" value="<?php echo $ye_max ?>">
                                            <div class="sidebar-action-units">
                                                <input id="stm-classic-filter-submit" class="hidden" type="submit" value="Mostrar carros">
                                                <a href="<?php echo SmartDealer::formAction() ?>" class="button"><span>Resetar tudo</span></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9 col-sm-12 ">
                                        <div class="stm-ajax-row">
                                            <div class="stm-car-listing-sort-units clearfix">
                                                <div class="stm-sort-by-options clearfix">
                                                    <span>Ordenar por:</span>
                                                    <div class="stm-select-sorting">
                                                        <div class="col-md-12 input-order">
                                                            <select class="form-control input-sm pull-right select-order" name="campo_ordenador"> 
                                                                <option value="modelo" <?php if (SmartDealer::formGet('campo_ordenador') == 'modelo') echo "selected=selected" ?>>Modelo</option>
                                                                <option value="cor" <?php if (SmartDealer::formGet('campo_ordenador') == 'cor') echo "selected=selected" ?>>Cor</option>
                                                                <option value="preco" <?php if (SmartDealer::formGet('campo_ordenador') == 'preco') echo "selected=selected" ?>>Preço</option>
                                                                <option value="ano" <?php if (SmartDealer::formGet('campo_ordenador') == 'ano') echo "selected=selected" ?>>Ano</option>
                                                            </select>
                                                            <input type="hidden" name="sentido_ordenacao" value="ASC">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="stm-view-by">
                                                    <a href="#" class="view-grid view-type " data-view="grid">
                                                        <i class="stm-icon-grid"></i>
                                                    </a>
                                                    <a href="#" class="view-list view-type active" data-view="list">
                                                        <i class="stm-icon-list"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div id="listings-result">
                                                <div class="stm-isotope-sorting stm-isotope-sorting-list">
                                                    <?php
                                                    $data = $this->getData();
                                                    ?>
                                                    <?php if ($data && is_array($data)): ?>
                                                        <?php foreach ($data as $row) : ?>
                                                            <div class="listing-list-loop stm-listing-directory-list-loop stm-isotope-listing-item">
                                                                <div class="image">
                                                                    <!--Video-->
                                                                    <a href="<?php echo SmartDealer::link($row['id'], $row['modelo']); ?>" class="rmv_txt_drctn">
                                                                        <div class="image-inner">
                                                                            <!--Badge-->
                                                                            <img width="300" height="auto" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwcHgiICBoZWlnaHQ9IjIwMHB4IiAgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB2aWV3Qm94PSIwIDAgMTAwIDEwMCIgcHJlc2VydmVBc3BlY3RSYXRpbz0ieE1pZFlNaWQiIGNsYXNzPSJsZHMtcm9sbGluZyIgc3R5bGU9ImJhY2tncm91bmQ6IG5vbmU7Ij48Y2lyY2xlIGN4PSI1MCIgY3k9IjUwIiBmaWxsPSJub25lIiBuZy1hdHRyLXN0cm9rZT0ie3tjb25maWcuY29sb3J9fSIgbmctYXR0ci1zdHJva2Utd2lkdGg9Int7Y29uZmlnLndpZHRofX0iIG5nLWF0dHItcj0ie3tjb25maWcucmFkaXVzfX0iIG5nLWF0dHItc3Ryb2tlLWRhc2hhcnJheT0ie3tjb25maWcuZGFzaGFycmF5fX0iIHN0cm9rZT0iIzZjOThlMSIgc3Ryb2tlLXdpZHRoPSIxMCIgcj0iMzUiIHN0cm9rZS1kYXNoYXJyYXk9IjE2NC45MzM2MTQzMTM0NjQxNSA1Ni45Nzc4NzE0Mzc4MjEzOCIgdHJhbnNmb3JtPSJyb3RhdGUoNjUuNzgxNCA1MCA1MCkiPjxhbmltYXRlVHJhbnNmb3JtIGF0dHJpYnV0ZU5hbWU9InRyYW5zZm9ybSIgdHlwZT0icm90YXRlIiBjYWxjTW9kZT0ibGluZWFyIiB2YWx1ZXM9IjAgNTAgNTA7MzYwIDUwIDUwIiBrZXlUaW1lcz0iMDsxIiBkdXI9IjFzIiBiZWdpbj0iMHMiIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIj48L2FuaW1hdGVUcmFuc2Zvcm0+PC9jaXJjbGU+PC9zdmc+" data-src="<?php echo SmartDealer::img($row['id'], null, null, 257, 1, true) ?>" alt="<?php echo $row['modelo']; ?>" class="img-responsive wp-post-image lazy" alt="">		
                                                                        </div>
                                                                    </a>
                                                                </div>
                                                                <div class="content">
                                                                    <div class="meta-top">
                                                                        <!--Price-->
                                                                        <div class="price">
                                                                            <?php if (intval($row['preco_promocional'])): ?>
                                                                                <div class="regular-price"><?php echo SmartDealer::preparePrice($row['preco'], 1, 0) ?></div>
                                                                                <div class="sale-price"><?php echo SmartDealer::preparePrice($row['preco_promocional'], 1, 0) ?></div>
                                                                            <?php else: ?>
                                                                                <div class="normal-price">
                                                                                    <span class="heading-font"><?php echo SmartDealer::preparePrice($row['preco'], 1, 0) ?></span>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        <!--Title-->
                                                                        <div class="title heading-font">
                                                                            <a href="<?php echo SmartDealer::link($row['id'], $row['modelo']); ?>" class="rmv_txt_drctn"><?php echo SmartDealer::prepareString($row['modelo'], true) ?></a>
                                                                        </div>
                                                                    </div>
                                                                    <!--Item parameters-->
                                                                    <div class="meta-middle">
                                                                        <div class="meta-middle">
                                                                            <div class="meta-middle-unit font-exists make">
                                                                                <div class="meta-middle-unit-top">
                                                                                    <div class="icon"><i class="fa fa-certificate"></i></div>
                                                                                    <div class="name">Marca</div>
                                                                                </div>
                                                                                <div class="value h5">
                                                                                    <?php echo SmartDealer::prepareName($row['marca']) ?>                        
                                                                                </div>
                                                                            </div>
                                                                            <div class="meta-middle-unit font-exists mileage">
                                                                                <div class="meta-middle-unit-top">
                                                                                    <div class="icon"><i class="stm-icon-road"></i></div>
                                                                                    <div class="name">Km</div>
                                                                                </div>
                                                                                <div class="value h5">
                                                                                    <?php echo ($row['km']) ?>                       
                                                                                </div>
                                                                            </div>
                                                                            <div class="meta-middle-unit font-exists fuel">
                                                                                <div class="meta-middle-unit-top">
                                                                                    <div class="icon"><i class="stm-icon-fuel"></i></div>
                                                                                    <div class="name">Combustível</div>
                                                                                </div>
                                                                                <div class="value h5">
                                                                                    <?php echo SmartDealer::prepareName($row['combustivel'], false) ?>                           
                                                                                </div>
                                                                            </div>
                                                                            <div class="meta-middle-unit font-exists engine">
                                                                                <div class="meta-middle-unit-top">
                                                                                    <div class="icon"><i class="stm-icon-engine_fill"></i></div>
                                                                                    <div class="name">Motor</div>
                                                                                </div>
                                                                                <div class="value h5">
                                                                                    <?php echo SmartDealer::prepareString($row['motor'], false) ?>                  
                                                                                </div>
                                                                            </div>
                                                                            <div class="meta-middle-unit font-exists ca-year">
                                                                                <div class="meta-middle-unit-top">
                                                                                    <div class="icon"><i class="fa fa-exchange"></i></div>
                                                                                    <div class="name">Ano</div>
                                                                                </div>
                                                                                <div class="value h5">
                                                                                    <?php $year = explode('/', $row['ano_fab_mod']); ?>
                                                                                    <?php echo SmartDealer::prepareYear(next($year)); ?>                     
                                                                                </div>
                                                                            </div>
                                                                            <div class="meta-middle-unit font-exists transmission">
                                                                                <div class="meta-middle-unit-top">
                                                                                    <div class="icon"><i class="stm-icon-transmission_fill"></i></div>
                                                                                    <div class="name">Transmissão</div>
                                                                                </div>
                                                                                <div class="value h5">
                                                                                    <?php echo SmartDealer::prepareString($row['transmissao'], false) ?>                         
                                                                                </div>
                                                                            </div>
                                                                            <div class="meta-middle-unit font-exists drive">
                                                                                <div class="meta-middle-unit-top">
                                                                                    <div class="icon"><i class="stm-icon-drive_2"></i></div>
                                                                                    <div class="name">Portas</div>
                                                                                </div>
                                                                                <div class="value h5">
                                                                                    <?php echo ($row['portas']) ?>                            
                                                                                </div>
                                                                            </div>
                                                                            <div class="meta-middle-unit font-exists exterior-color">
                                                                                <div class="meta-middle-unit-top">
                                                                                    <div class="icon"><i class="stm-boats-icon-narrow-boat-painting"></i></div>
                                                                                    <div class="name">Cor externa</div>
                                                                                </div>
                                                                                <div class="value h5">
                                                                                    <?php echo SmartDealer::prepareName($row['cor']) ?>                        
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <!--Item options-->
                                                                    <div class="meta-bottom">
                                                                        <div class="single-car-actions">
                                                                            <ul class="list-unstyled clearfix">
                                                                                <!--Stock num-->
                                                                                <li>
                                                                                    <div class="stock-num heading-font"><span>CÓD #</span><?php echo ($row['id']) ?></div>
                                                                                </li>
                                                                                <!--Schedule-->
                                                                                <li>
                                                                                    <a href="#" class="car-action-unit stm-schedule" data-toggle="modal" data-target="#test-drive" onclick="stm_test_drive_car_title('<?php echo $row['veiculo_chassi'] ?>', '<?php echo $row['modelo']; ?>');">
                                                                                        <i class="stm-icon-steering_wheel"></i>
                                                                                        Agendar Best Drive				</a>
                                                                                </li>
                                                                                <!--COmpare-->
                                                                                <!--li>
                                                                                    <a href="#" class="car-action-unit add-to-compare" data-id="<?php echo ($row['id']) ?>" data-action="add">
                                                                                        <i class="stm-icon-add"></i>
                                                                                        Add. p/ comparar					</a>
                                                                                </li-->
                                                                                <!--PDF-->
                                                                                <!--Share-->
                                                                                <!--Certified Logo 1-->
                                                                                <!--Certified Logo 2-->
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <div class=col-sm-12>
                                                            <center>
                                                                <h3>Não foram encontrados resultados</h3>
                                                            </center>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="stm_ajax_pagination stm-blog-pagination">
                                                    <?php $pager = $this->getPager($data); ?>
                                                    <ul class="page-numbers"> 
                                                        <?php foreach ($pager as $link): ?>
                                                            <li class="<?php echo $link['class']; ?>">
                                                                <?php if ($link['atual']) : ?>
                                                                    <span aria-current="page" class="page-numbers current"><?php echo $link['text']; ?></span>
                                                                <?php else: ?>
                                                                    <a href="<?php echo ($link['atual']) ? 'javascript:return false' : $link['link']; ?>">
                                                                        <?php echo $link['text']; ?>
                                                                    </a>
                                                                <?php endif; ?>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--col-md-9-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix" style="margin-bottom: 40px;"></div>
    </div>
</form>

<!-- page scripts -->
<script type="text/javascript" src="<?php echo SmartDealer::url('js/estoque.js'); ?>"></script>