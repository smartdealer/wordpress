<?php
/**
 * The template body
 *
 * @package   Smart Dealer Wordpress Plugin
 * @author    Patrick Otto <patrick@smartdealership.com.br>
 * @version   2.0
 * @access    public
 * @copyright Smart Dealer(c), Mar 2017
 * @see       http://www.smartdealer.com.br
 */
$totais = $api->getTotais();

$valid = (!empty($totais) && is_array($totais) && key_exists(0, $totais)) && key_exists('preco_min', $totais[0]);
$pr_min = (int) ($valid) ? min(\SmartDealer::array_column((array) $totais, 'preco_min')) : 0;
$pr_max = (int) ($valid) ? max(\SmartDealer::array_column((array) $totais, 'preco_max')) : 0;
$ye_min = (int) ($valid) ? min(\SmartDealer::array_column((array) $totais, 'ano_min')) : date('Y');
$ye_max = (int) ($valid) ? max(\SmartDealer::array_column((array) $totais, 'ano_max')) : date('Y');
?>
<div class="row sd-scope">
    <form action="<?php echo SmartDealer::formAction() ?>" name="busca" method=get>
        <div class="sd-container">    
            <div class="row">    
                <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12">
                    <div class="form-filter">
                        <h4 class="page-header">Filtre sua pesquisa</h4>
                        <div class="clearfix clear"></div>
                        <div class="form-group">
                            <input value="<?php echo SmartDealer::formGet('query') ?>" placeholder="Buscar orgânica.." name="query" class="form-control"/>
                        </div>
                        <div class="clearfix clear"></div>
                        <?php $a = $api->getModels(); ?>
                        <div class="form-group">
                            <select class=form-control name=modelo>
                                <option value="">Modelo</option>
                                <?php foreach ($a as $row): ?>
                                    <option value="<?php echo $row['item'] ?>" <?php if (SmartDealer::formGet('modelo') == $row['item']) echo "selected=selected" ?>><?php echo $row['item'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php $a = $api->getColors(); ?>
                        <div class="form-group">
                            <select class=form-control name=cor>
                                <option value="">Cor</option>
                                <?php foreach ($a as $row): ?>
                                    <option value="<?php echo $row['item'] ?>" <?php if (SmartDealer::formGet('cor') == $row['item']) echo "selected=selected" ?>><?php echo $row['item'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php $a = $api->getFuels(); ?>
                        <div class="form-group">
                            <select class=form-control name=combustivel>
                                <option value="">Combustível</option>
                                <?php foreach ($a as $row): ?>
                                    <option value="<?php echo $row['item'] ?>" <?php if (SmartDealer::formGet('combustivel') == $row['item']) echo "selected=selected" ?>><?php echo $row['item'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="clearfix"></div>
                        <div class="filter-label">Faixa de Ano</div> 
                        <div class="clearfix"></div>
                        <div class="col-xs-12 range-slider-gp">
                            <div id="range-ano"></div>
                            <div class="clearfix"></div>
                            <div class="range left" id="range-min-ano"></div>
                            <div class="range right" id="range-max-ano"></div>
                            <input id="ano-min" name="ano_min" type="hidden" value="0"/>
                            <input id="ano-max" name="ano_max" type="hidden" value="0"/>
                        </div>    
                        <div class="clearfix"></div>
                        <div class="filter-label">Faixa de Preço</div> 
                        <div class="clearfix"></div>
                        <div class="col-xs-12 range-slider-gp">
                            <div id="range-preco"></div>
                            <div class="clearfix"></div>
                            <div class="range left" id="range-min"></div>
                            <div class="range right" id="range-max"></div>
                            <input id="preco-min" name="preco_min" type="hidden" value="0"/>
                            <input id="preco-max" name="preco_max" type="hidden" value="0"/>
                        </div>    
                        <div class="clear clearfix"></div>
                        <div id=form_register_btn class=text-center>
                            <input class="btn btn-primary btn-lg" type=submit value=Buscar style="padding: 10px 45px;" id=submit>
                        </div>
                        <div class="clear clearfix"></div>
                    </div>
                </div>
                <div class="col-lg-9 col-md-8 col-sm-12 col-xs-12">
                    <div class="col-md-5 col-xs-12 pull-right no-padding">
                        <div class="col-md-6 col-xs-12 input-order">
                            <select class="form-control input-sm pull-right select-order" name="campo_ordenador"> 
                                <option value="modelo" <?php if (SmartDealer::formGet('campo_ordenador') == 'modelo') echo "selected=selected" ?>>Modelo</option>
                                <option value="cor" <?php if (SmartDealer::formGet('campo_ordenador') == 'cor') echo "selected=selected" ?>>Cor</option>
                                <option value="preco" <?php if (SmartDealer::formGet('campo_ordenador') == 'preco') echo "selected=selected" ?>>Preço</option>
                                <option value="ano" <?php if (SmartDealer::formGet('campo_ordenador') == 'ano') echo "selected=selected" ?>>Ano</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-xs-12 input-order no-padding">
                            <select class="form-control input-sm pull-right select-order" name="sentido_ordenacao">
                                <option value="ASC" <?php if (SmartDealer::formGet('sentido_ordenacao') == 'ASC') echo "selected=selected" ?>>Crescente</option>
                                <option value="DESC" <?php if (SmartDealer::formGet('sentido_ordenacao') == 'DESC') echo "selected=selected" ?>>Decrescente</option>
                            </select>
                        </div>
                    </div>
                    <div class="clear clearfix"></div>
                    <div class="list-item">
                        <?php $data = $api->getData(); ?>
                        <?php if ($data && is_array($data)): ?>
                            <?php foreach ($data as $row) : ?>
                                <?php $img = explode('/', substr($row['url_imagem'], 0, -4)); ?>
                                <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 row-item">
                                    <div class="content-item">
                                        <a href="<?php echo SmartDealer::link($row['veiculo_chassi'], $row['modelo']); ?>">
                                            <div class="image-item">
                                                <img src="<?php echo SmartDealer::img($row['id'], $img[0], $img[1], 250) ?>" alt="<?php echo $row['modelo']; ?>" class="img-responsive">
                                            </div>
                                            <h3><?php echo SmartDealer::prepareName($row['modelo']) ?></h3>
                                            <div class="col-md-6 no-padding text-left">
                                                <?php $year = explode('/', $row['ano_fab_mod']); ?>
                                                <div class="year-item"><?php echo(SmartDealer::prepareYear(current($year)) . '/' . SmartDealer::prepareYear(next($year))); ?></div>
                                            </div>
                                            <div class="col-md-6 no-padding text-right">
                                                <div class="price-item"><?php echo SmartDealer::preparePrice($row['preco'], 1) ?></div>
                                            </div>
                                            <div class="col-md-6 no-padding text-left">
                                                <div class="color-item"><?php echo SmartDealer::prepareName($row['cor']) ?></div>
                                            </div>
                                            <div class="col-md-6 no-padding text-right">
                                                <div class="km-item">0 KM</div>
                                            </div>
                                        </a>
                                        <div class="clearfix clear"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class=col-sm-12>
                            <center>
                                <h3>Não foram encontrados resultados</h3>
                            </center>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="clear clearfix"></div>
                <div class="col-lg-9 col-md-8 col-sm-12 col-xs-12 col-md-offset-3">
                    <?php $pager = $api->getPager($data); ?>
                    <center>
                        <nav>
                            <ul class="pagination">
                                <?php foreach ($pager as $link): ?>
                                    <li class="<?php if ($link['atual']) echo 'active'; ?>">
                                        <a href="<?php echo ($link['atual']) ? 'javascript:return false' : $link['link']; ?>">
                                            <?php echo $link['text']; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </nav>
                    </center>
                    <div class="clear clearfix"></div>
                </div> 
            </div>
        </div>
        <input type=hidden name="busca" value="<?php echo SmartDealer::formGet('busca', '') ?>">
        <input type=hidden name="st_preco_min" value="<?php echo $pr_min ?>">
        <input type=hidden name="st_preco_max" value="<?php echo $pr_max ?>">
        <input type=hidden name="st_ano_min" value="<?php echo $ye_min ?>">
        <input type=hidden name="st_ano_max" value="<?php echo $ye_max ?>">
    </form>
</div>

<!-- page styles -->
<link href="<?php echo SmartDealer::url('css/jquery-ui.min.css'); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo SmartDealer::url('css/bootstrap-minimal.css'); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo SmartDealer::url('css/tpl-style.css'); ?>" rel="stylesheet" type="text/css"/>

<!-- page scripts -->
<script type="text/javascript" src="<?php echo SmartDealer::url('js/jquery-ui.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo SmartDealer::url('js/estoque.js'); ?>"></script>