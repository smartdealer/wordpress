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
$url_novos = $this->getLinkFlag('novos');
$url_usados = $this->getLinkFlag('usados');
?>
<style type="text/css">
    .car-listing-tabs-unit .car-listing-top-part:before {
        background-color: #232628		
    }

    .car-listing-row .listing-car-item-meta .price.discounted-price .sale-price {
        line-height: 16px;
        min-height: 19px;
        line-height: 20px;
    }

    #form-filter select {
        height: auto !important;
        margin: auto !important;
        position: relative !important;
    }

</style>
<script type="text/javascript">

    stm_lang_code = (typeof stm_lang_code === 'string') ? stm_lang_code : 'pt_br';
    ajaxurl_smart = '<?php echo $this->urlBase('smartdealer_data'); ?>';
    ajaxurl = (typeof ajaxurl === 'string') ? ajaxurl : '<?php echo SmartDealer::formAction() ?>';

    STMListings = (typeof STMListings === 'object') ? STMListings : {
        Filter: {prototype: {
                ajaxBefore: function () {},
                ajaxSuccess: function () {}
            }}};

    SmartDealer.addEvent(function () {
        $('select[id^=filter-]').on('change', function (e) {

            type = ($('#filter-type option:selected').text().toLowerCase() === 'novos') ? 'novos' : 'usados';
            params = $('#form-filter').serialize();
            reset = $('#filter-type').attr('data-last-value') !== type;

            $('#filter-type').attr('data-last-value', type);
            $('#form-filter').attr('action', $('#filter-type').val());

            if ((e.target.id === "filter-type") || reset) {

                $('#filter-mark').html('<option value="" selected="selected">Marca</option>');

                $.get(ajaxurl_smart + type + '/marca?' + params, function (a) {
                    if (a) {
                        for (var i in a) {
                            $('#filter-mark').append('<option value="' + a[i].item + '">' + a[i].item + '</option>');
                        }
                    }
                });
            }

            if ((e.target.id === "filter-mark") || reset) {

                $('#filter-family').html('<option value="" selected="selected">Família</option>');

                $.get(ajaxurl_smart + type + '/familia?' + params, function (a) {
                    if (a) {
                        for (var i in a) {
                            $('#filter-family').append('<option value="' + a[i].item + '">' + a[i].item + '</option>');
                        }

                    }
                });
            }

            if ((!e.target.id.match(/price/i) && $('#filter-price-min').val() !== $('#filter-price-min').attr('data-last-value')) || reset) {

                $('#filter-price-min').html('<option value="" selected="selected">Preço mínimo</option>');

                if (reset) {
                    $('#filter-price-min').attr('data-last-value', '');
                } else {
                    $('#filter-price-min').attr('data-last-value', $('#filter-price-min').val());
                }

                $.get(ajaxurl_smart + type + '/total?' + params, function (a) {
                    if (a) {
                        for (var i in a) {
                            $('#filter-price-min').append('<option value = "' + a[i] + '"> ' + a[i] + ' </option>');
                        }
                    }
                });
            }

            if ((!e.target.id.match(/price/i) && $('#filter-price-max').val() !== $('#filter-price-max').attr('data-last-value')) || reset) {
                $('#filter-price-max').html('<option value="" selected="selected">Preço máximo</option>');

                if (reset) {
                    $('#filter-price-max').attr('data-last-value', '');
                } else {
                    $('#filter-price-max').attr('data-last-value', $('#filter-price-max').val());
                }

                $.get(ajaxurl_smart + type + '/total?' + params, function (a) {
                    if (a) {
                        for (var i in a) {
                            $('#filter-price-max').append('<option value = "' + a[i] + '"> ' + a[i] + ' </option>');
                        }
                    }
                });
            }

        });

        $('#form-filter').find('option:eq(1)').prop('selected', true).change();
    });

</script>
<div class="vc_row wpb_row vc_row-fluid">
    <div class="wpb_column vc_column_container vc_col-sm-12">
        <div class="vc_column-inner ">
            <div class="wpb_wrapper">
                <div class="car-listing-tabs-unit listing-cars-id-88310">
                    <div class="car-listing-top-part">
                        <?php
                        $data = array_merge($data_novos = $this->getOffers('novo', 8, true), $data_usados = $this->getOffers('usado', 8, true));
                        ?>
                        <div class="found-cars-cloned">
                            <div class="found-cars heading-font"><i class="stm-icon-car"></i>Disponível <span class="blue-lt"><?php echo count($data) ?> ofertas</span></div>
                        </div>
                        <div class="title">
                            <h2><span class="stm-base-color">EM ESTOQUE</span></h2>
                        </div>
                        <div class="stm-listing-tabs">
                            <ul class="heading-font" role="tablist">
                                <li class="active">
                                    <a href="#car-listing-category-ofertas" role="tab" data-toggle="tab">
                                        Ofertas 										
                                    </a>
                                </li>
                                <li>
                                    <a href="#car-listing-category-novos" role="tab" data-toggle="tab">
                                        Novos 										
                                    </a>
                                </li>
                                <li>
                                    <a href="#car-listing-category-usados" role="tab" data-toggle="tab">
                                        Usados 										
                                    </a>
                                </li>
                                <li>
                                    <a href="#car-listing-category-busca" role="tab" data-toggle="tab">
                                        Pesquisar carro 										
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="car-listing-main-part">
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="car-listing-category-ofertas">
                                <div class="row row-4 car-listing-row">
                                    <?php $this->setModal('novo'); ?>
                                    <?php if ($data_novos && is_array($data_novos)): ?>
                                        <?php foreach ($data_novos as $row) : ?>
                                            <div class="col-md-3 col-sm-4 col-xs-12 col-xxs-12 stm-template-front-loop">
                                                <a href="<?php echo SmartDealer::link($row['veiculo_chassi'], $row['modelo']) ?>" class="rmv_txt_drctn xx">
                                                    <div class="image">
                                                        <img width="255" height="auto" style="max-height: 138px" src="<?php echo SmartDealer::urlPlugin('template/default/assets/img/pre-img.jpg'); ?>" data-src="<?php
                                                        $img = explode('/', substr($row['url_imagem'], 0, -4));
                                                        echo SmartDealer::img($row['id'], $img[0], $img[1], 257, 1, false);
                                                        ?>" alt="<?php echo $row['modelo']; ?>" class="attachment-stm-img-255-135 size-stm-img-255-135 lazy">								
                                                    </div>
                                                    <div class="listing-car-item-meta">
                                                        <div class="car-meta-top heading-font clearfix">
                                                            <div class="price discounted-price">
                                                                <?php if (intval($row['preco_promocional'])): ?>
                                                                    <div class="regular-price"><?php echo SmartDealer::preparePrice($row['preco'], 1, 0) ?></div>
                                                                    <div class="sale-price"><?php echo SmartDealer::preparePrice($row['preco_promocional'], 1, 0) ?></div>
                                                                <?php else: ?>
                                                                    <div class="sale-price"><?php echo SmartDealer::preparePrice($row['preco'], 1, 0) ?></div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="car-title">
                                                                <?php echo SmartDealer::prepareString($row['modelo'], true) ?>								
                                                            </div>
                                                        </div>
                                                        <div class="car-meta-bottom">
                                                            <ul>
                                                                <li>
                                                                    <i class="stm-icon-road"></i>
                                                                    <span><?php echo ($row['tipo'] === 'novo') ? 0 : $row['km']; ?>KM</span>
                                                                </li>
                                                                <li>
                                                                    <i class="stm-icon-transmission_fill"></i>
                                                                    <span><?php echo SmartDealer::prepareString($row['transmissao'], false) ?></span>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <?php $this->setModal('usado'); ?>
                                    <?php if ($data_usados && is_array($data)): ?>
                                        <?php foreach ($data_usados as $row) : ?>
                                            <div class="col-md-3 col-sm-4 col-xs-12 col-xxs-12 stm-template-front-loop">
                                                <a href="<?php echo SmartDealer::link($row['id'], $row['modelo']); ?>" class="rmv_txt_drctn xx">
                                                    <div class="image">
                                                        <img width="255" height="auto" style="max-height: 138px" src="<?php echo SmartDealer::urlPlugin('template/default/assets/img/pre-img.jpg'); ?>" data-src="<?php echo SmartDealer::img($row['id'], null, null, 257, 1, true) ?>" alt="<?php echo $row['modelo']; ?>" class="attachment-stm-img-255-135 size-stm-img-255-135 lazy">							
                                                    </div>
                                                    <div class="listing-car-item-meta">
                                                        <div class="car-meta-top heading-font clearfix">
                                                            <div class="price discounted-price">
                                                                <?php if (intval($row['preco_promocional'])): ?>
                                                                    <div class="regular-price"><?php echo SmartDealer::preparePrice($row['preco'], 1, 0) ?></div>
                                                                    <div class="sale-price"><?php echo SmartDealer::preparePrice($row['preco_promocional'], 1, 0) ?></div>
                                                                <?php else: ?>
                                                                    <div class="sale-price"><?php echo SmartDealer::preparePrice($row['preco'], 1, 0) ?></div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="car-title">
                                                                <?php echo SmartDealer::prepareString($row['modelo'], true) ?>								
                                                            </div>
                                                        </div>
                                                        <div class="car-meta-bottom">
                                                            <ul>
                                                                <li>
                                                                    <i class="stm-icon-road"></i>
                                                                    <span><?php echo ($row['tipo'] === 'novo') ? 0 : $row['km']; ?>KM</span>
                                                                </li>
                                                                <li>
                                                                    <i class="stm-icon-transmission_fill"></i>
                                                                    <span><?php echo SmartDealer::prepareString($row['transmissao'], false) ?></span>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="row car-listing-actions">
                                    <div class="col-xs-12 text-center">
                                        <div class="dp-in" style="padding-bottom: 25px;">
                                            <div class="preloader">
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                            </div>
                                            <a class="load-more-btn" href="<?php echo $this->getLinkFlag('novos'); ?>">
                                                Ver mais	
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="car-listing-category-novos">
                                <?php $data = $this->getOffers('novo', 8); ?>
                                <div class="row row-4 car-listing-row">
                                    <?php if ($data && is_array($data)): ?>
                                        <?php foreach ($data as $row) : ?>
                                            <?php $img = explode('/', substr($row['url_imagem'], 0, -4)); ?>
                                            <div class="col-md-3 col-sm-4 col-xs-12 col-xxs-12 stm-template-front-loop">
                                                <a href="<?php echo SmartDealer::link($row['veiculo_chassi'], $row['modelo']); ?>" class="rmv_txt_drctn xx">
                                                    <div class="image">
                                                        <img width="255" height="auto" style="max-height: 138px" src="<?php echo SmartDealer::urlPlugin('template/default/assets/img/pre-img.jpg'); ?>" data-src="<?php
                                                        $img = explode('/', substr($row['url_imagem'], 0, -4));
                                                        echo SmartDealer::img($row['id'], $img[0], $img[1], 257, 1, false); ?>" alt="<?php echo $row['modelo']; ?>" class="attachment-stm-img-255-135 size-stm-img-255-135 lazy">								
                                                    </div>
                                                    <div class="listing-car-item-meta">
                                                        <div class="car-meta-top heading-font clearfix">
                                                            <div class="price discounted-price">
                                                                <?php if (intval($row['preco_promocional'])): ?>
                                                                    <div class="regular-price"><?php echo SmartDealer::preparePrice($row['preco'], 1, 0) ?></div>
                                                                    <div class="sale-price"><?php echo SmartDealer::preparePrice($row['preco_promocional'], 1, 0) ?></div>
                                                                <?php else: ?>
                                                                    <div class="sale-price"><?php echo SmartDealer::preparePrice($row['preco'], 1, 0) ?></div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="car-title">
                                                                <?php echo SmartDealer::prepareString($row['modelo'], true) ?>								
                                                            </div>
                                                        </div>
                                                        <div class="car-meta-bottom">
                                                            <ul>
                                                                <li>
                                                                    <i class="stm-icon-road"></i>
                                                                    <span>OKM</span>
                                                                </li>
                                                                <li>
                                                                    <i class="stm-icon-transmission_fill"></i>
                                                                    <span><?php echo SmartDealer::prepareString($row['transmissao'], false) ?></span>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </a>
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
                                <div class="row car-listing-actions">
                                    <div class="col-xs-12 text-center">
                                        <div class="dp-in" style="padding-bottom: 25px;">
                                            <div class="preloader">
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                            </div>
                                            <a class="load-more-btn" href="<?php echo $this->getLinkFlag('novos'); ?>">
                                                Ver mais	
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="car-listing-category-usados">
                                <?php $data = $this->getOffers('usado', 8); ?>
                                <div class="row row-4 car-listing-row">
                                    <?php if ($data && is_array($data)): ?>
                                        <?php foreach ($data as $row) : ?>
                                            <div class="col-md-3 col-sm-4 col-xs-12 col-xxs-12 stm-template-front-loop">
                                                <a href="<?php echo SmartDealer::link($row['id'], $row['modelo']); ?>" class="rmv_txt_drctn xx">
                                                    <div class="image">
                                                        <img width="255" height="auto" style="max-height: 138px" src="<?php echo SmartDealer::urlPlugin('template/default/assets/img/pre-img.jpg'); ?>" data-src="<?php echo SmartDealer::img($row['id'], null, null, 257, 1, true) ?>" alt="<?php echo $row['modelo']; ?>" class="attachment-stm-img-255-135 size-stm-img-255-135 lazy">								
                                                    </div>
                                                    <div class="listing-car-item-meta">
                                                        <div class="car-meta-top heading-font clearfix">
                                                            <div class="price discounted-price">
                                                                <?php if (intval($row['preco_promocional'])): ?>
                                                                    <div class="regular-price"><?php echo SmartDealer::preparePrice($row['preco'], 1, 0) ?></div>
                                                                    <div class="sale-price"><?php echo SmartDealer::preparePrice($row['preco_promocional'], 1, 0) ?></div>
                                                                <?php else: ?>
                                                                    <div class="sale-price"><?php echo SmartDealer::preparePrice($row['preco'], 1, 0) ?></div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="car-title">
                                                                <?php echo SmartDealer::prepareString($row['modelo'], true) ?>								
                                                            </div>
                                                        </div>
                                                        <div class="car-meta-bottom">
                                                            <ul>
                                                                <li>
                                                                    <i class="stm-icon-road"></i>
                                                                    <span><?php echo $row['km']; ?>KM</span>
                                                                </li>
                                                                <li>
                                                                    <i class="stm-icon-transmission_fill"></i>
                                                                    <span><?php echo SmartDealer::prepareString($row['transmissao'], false) ?></span>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </a>
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
                                <div class="row car-listing-actions">
                                    <div class="col-xs-12 text-center">
                                        <div class="dp-in" style="padding-bottom: 25px;">
                                            <div class="preloader">
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                            </div>
                                            <a class="load-more-btn" href="<?php echo $this->getLinkFlag('usados'); ?>">
                                                Ver mais	
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="car-listing-category-busca" style="padding-top: 10px;padding-bottom: 10px;">
                                <div class="row row-4 car-listing-row">
                                    <form id="form-filter">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <select id="filter-type">
                                                    <option value="">Estoque</option>
                                                    <?php if ($url_novos): ?>
                                                        <option value="<?php echo $url_novos; ?>">Novos</option>
                                                    <?php endif; ?>
                                                    <?php if ($url_usados): ?>
                                                        <option value="<?php echo $url_usados; ?>">Seminovos</option>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <select id="filter-mark" name="marca">
                                                    <option value="" selected="selected">Carregando..</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <select id="filter-family" name="familia">
                                                    <option value="" selected="selected">Carregando..</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="clear clearfix"></div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <select id="filter-price-min" name="preco_min">
                                                    <option value="" selected="selected">Carregando..</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <select id="filter-price-max" name="preco_max">
                                                    <option value="" selected="selected">Carregando..</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <button type="submit" class="load-more-btn pull-right btn-block" style="margin-top: 21px;">
                                                    Buscar	
                                                </button>
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