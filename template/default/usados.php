<?php

/**
 * The template body
 *
 * @package   Smart Dealer Wordpress Plugin
 * @author    Patrick Otto <patrick@smartdealership.com.br>
 * @author    Jean Carlos dos Santos <jean@smartdealership.com.br>
 * @version   2.0.5
 * @access    public
 * @copyright Smart Dealer(c), 2017-2019
 * @see       http://smartdealer.com.br
 */

$totais = $this->getTotals();

$valid = (!empty($totais) && is_array($totais) && key_exists(0, $totais)) && key_exists('preco_min', $totais[0]);
$pr_min = (int) ($valid) ? min(\SmartDealer::array_column((array) $totais, 'preco_min')) : 0;
$pr_max = (int) ($valid) ? max(\SmartDealer::array_column((array) $totais, 'preco_max')) : 0;
$ye_min = (int) ($valid) ? min(\SmartDealer::array_column((array) $totais, 'ano_min')) : date('Y');
$ye_max = (int) ($valid) ? max(\SmartDealer::array_column((array) $totais, 'ano_max')) : date('Y');

?>

<div class="sd-scope row">

    <section>
        <div class=container>
            <div class="class-md-12 text-center">
                <h2>Veículos Usados</h2>
                <h4>As melhores ofertas da região</h4>
            </div>
    </section>

    <form action="<?php echo SmartDealer::formAction() ?>" name="busca" method="get">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12" style="margin-bottom: 15px;">
                    <div class=form-register>
                        <div class="col-sm-12 text-center pad5 filter-title">
                            <h4>Busca Direta</h4>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-sm-12 input-filter">
                            <div class="form-group">
                                <input type="text" name="busca_smart" class="form-control" placeholder="Digite o que procura..." value="<?php echo SmartDealer::formGet('busca_smart', ''); ?>" aria-label="Digite o que procura..." style="width:80%;display:inline;" />
                                <button class="btn btn-primary pull-right" type="submit" style="display: table-cell;"><i class="glyphicon glyphicon-search"></i></button>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-sm-12 text-center pad5">
                            <h4>Filtrar Pesquisa</h4>
                        </div>
                        <div class="col-sm-12 input-filter">
                            <div class="form-group">
                                <select name="marca" class="form-control">
                                    <option value="" selected="selected">Marca</option>
                                    <?php $a = $this->getMarks(); ?>
                                    <?php foreach ($a as $row) : ?>
                                        <option value="<?php echo $row['item'] ?>" <?php if (SmartDealer::formGet('marca') == $row['item']) echo "selected=selected" ?>><?php echo SmartDealer::prepareString($row['item'], false) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12 input-filter">
                            <div class="form-group">
                                <select name="modelo" class="form-control">
                                    <option value="" selected="selected">Modelo</option>
                                    <?php $a = $this->getModels(); ?>
                                    <?php foreach ($a as $row) : ?>
                                        <option value="<?php echo $row['item'] ?>" <?php if (SmartDealer::formGet('modelo') == $row['item']) echo "selected=selected" ?>><?php echo SmartDealer::prepareString($row['item'], false) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12 input-filter">
                            <div class="form-group">
                                <select name="cor" class="form-control">
                                    <option value="" selected="selected">Cor</option>
                                    <?php $a = $this->getColors(); ?>
                                    <?php foreach ($a as $row) : ?>
                                        <option value="<?php echo $row['item'] ?>" <?php if (SmartDealer::formGet('cor') == $row['item']) echo "selected=selected" ?>><?php echo $row['item'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12 input-filter">
                            <div class="form-group">
                                <select name="combustivel" class="form-control">
                                    <option value="" selected="selected">Combustível</option>
                                    <?php $a = $this->getFuels(); ?>
                                    <?php foreach ($a as $row) : ?>
                                        <option value="<?php echo $row['item'] ?>" <?php if (SmartDealer::formGet('combustivel') == $row['item']) echo "selected=selected" ?>><?php echo $row['item'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="clearfix"></div>

                        <div class="input-label">Faixa de Ano</div>

                        <div class="clearfix"></div>

                        <div class="col-xs-12 pad5 range-slider-gp">
                            <div id="range-ano"></div>
                            <div class="clearfix"></div>
                            <div class="col-lg-6 range left" id="range-min-ano"></div>
                            <div class="col-lg-6 range right" id="range-max-ano"></div>
                            <input id="ano-min" name="ano_min" type="hidden" value="<?php echo SmartDealer::formGet('ano_min', 0) ?>" />
                            <input id="ano-max" name="ano_max" type="hidden" value="<?php echo SmartDealer::formGet('ano_max', 0) ?>" />
                        </div>

                        <div class="clearfix"></div>

                        <div class="input-label">Faixa de Preço</div>

                        <div class="clearfix"></div>

                        <div class="col-xs-12 pad5 range-slider-gp">
                            <div id="range-preco"></div>
                            <div class="clearfix"></div>
                            <div class="col-lg-6 range left" id="range-min"></div>
                            <div class="col-lg-6 range right" id="range-max"></div>
                            <input id="preco-min" name="preco_min" type="hidden" value="<?php echo SmartDealer::formGet('preco_min', 0) ?>" />
                            <input id="preco-max" name="preco_max" type="hidden" value="<?php echo SmartDealer::formGet('preco_max', 0) ?>" />
                        </div>

                        <div class="clear clearfix"></div>

                        <div id=form_register_btn class=text-center>
                            <input class="btn btn-primary btn-lg" type=submit value=Buscar style="padding: 10px 45px;" id=submit>
                        </div>

                        <div class="clear clearfix"></div>

                    </div>
                </div>

                <div class="col-lg-9 col-md-8 col-sm-12 col-xs-12">
                    <div class="col-lg-12 no-padding m-b-10">
                        <div style="float: right;">
                            <select class="select-order" name="campo_ordenador">
                                <option value="modelo" <?php if (SmartDealer::formGet('campo_ordenador') == 'modelo') echo "selected=selected" ?>>Modelo</option>
                                <option value="cor" <?php if (SmartDealer::formGet('campo_ordenador') == 'cor') echo "selected=selected" ?>>Cor</option>
                                <option value="preco" <?php if (SmartDealer::formGet('campo_ordenador') == 'preco') echo "selected=selected" ?>>Preço</option>
                                <option value="ano" <?php if (SmartDealer::formGet('campo_ordenador') == 'ano') echo "selected=selected" ?>>Ano</option>
                            </select>
                            <select class="select-order" name="sentido_ordenacao">
                                <option value="ASC" <?php if (SmartDealer::formGet('sentido_ordenacao') == 'ASC') echo "selected=selected" ?>>Crescente</option>
                                <option value="DESC" <?php if (SmartDealer::formGet('sentido_ordenacao') == 'DESC') echo "selected=selected" ?>>Decrescente</option>
                            </select>
                        </div>
                    </div>

                    <div class="clear clearfix" style="height: 10px;"></div>

                    <div id="list-group">
                        <?php $data = $this->getData(); ?>
                        <?php if ($data && is_array($data)) : ?>
                            <?php foreach ($data as $row) : ?>

                                <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 row-item" id="gallery">
                                    <div class="item">
                                        <a href="<?php echo SmartDealer::link($row['id'], $row['modelo']); ?>">
                                            <div class="item-image">
                                                <img src="<?php echo SmartDealer::img($row['id'], null, null, 350, 1, true) ?>" alt="<?php echo $row['modelo'] ?>" class="img-responsive">
                                            </div>
                                            <h3><?php echo SmartDealer::prepareName($row['marca']) ?> <?php echo SmartDealer::prepareName($row['modelo']) ?></h3>
                                            <?php $year = explode('/', $row['ano_fab_mod']); ?>
                                            <h4><?php echo (SmartDealer::prepareYear(current($year)) . '/' . SmartDealer::prepareYear(next($year))); ?></h4>
                                            <div class="price-label"><?php echo SmartDealer::preparePrice($row['preco'], 1, 0) ?></div>
                                        </a>
                                    </div>
                                </div>

                            <?php endforeach; ?>

                        </div>
                    <?php else : ?>
                        <div class=col-sm-12>
                            <center>
                                <h3>Não foram encontrados resultados</h3>
                            </center>
                        </div>
                    <?php endif; ?>
                </div>

                <?php $pager = $this->getPager($data); ?>

                <center>
                    <nav>
                        <ul class="pagination">
                            <?php foreach ($pager as $link) : ?>
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

                <!--col-md-9-->
            </div>

            <div class="clearfix" style="margin-bottom: 40px;"></div>
        </div>
    </form>

</div>

<!-- page scripts -->
<script type="text/javascript">
    SmartDealer.setComplete(function(j) {
        function money_format(n) {
            var p = n.toFixed(2).split(".");
            return "R$ " + p[0].split("").reverse().reduce(function(acc, n, i) {
                return n + (i && !(i % 3) ? "." : "") + acc;
            }, "") + "," + p[1];
        }

        function set_price_range(a, b) {
            jQuery("#range-min").text(money_format(a));
            jQuery("#range-max").text(money_format(b));
            jQuery("#preco-min").val(a);
            jQuery("#preco-max").val(b);
        }

        function set_year_range(a, b) {
            jQuery("#range-min-ano").text(a);
            jQuery("#range-max-ano").text(b);
            jQuery("#ano-min").val(a);
            jQuery("#ano-max").val(b);
        }

        set_price_range(<?php echo (int) SmartDealer::formGet('preco_min', $pr_min); ?>, <?php echo (int) SmartDealer::formGet('preco_max', $pr_max); ?>);

        jQuery("#range-preco").slider({
            range: true,
            min: <?php echo $pr_min; ?>,
            max: <?php echo $pr_max; ?>,
            values: [<?php echo (int) SmartDealer::formGet('preco_min', $pr_min); ?>, <?php echo (int) SmartDealer::formGet('preco_max', $pr_max); ?>],
            step: 5000,
            slide: function(event, ui) {
                set_price_range(ui.values[0], ui.values[1]);
            }
        });

        set_year_range(<?php echo (int) SmartDealer::formGet('ano_min', $ye_min); ?>, <?php echo (int) SmartDealer::formGet('ano_max', $ye_max); ?>);

        jQuery("#range-ano").slider({
            range: true,
            min: <?php echo $ye_min; ?>,
            max: <?php echo $ye_max; ?>,
            values: [<?php echo (int) SmartDealer::formGet('ano_min', $ye_min); ?>, <?php echo (int) SmartDealer::formGet('ano_max', $ye_max); ?>],
            step: 1,
            slide: function(event, ui) {
                set_year_range(ui.values[0], ui.values[1]);
            }
        });

        jQuery('.select-order').on('change', function() {
            $('#submit').click();
        });
    });
</script>