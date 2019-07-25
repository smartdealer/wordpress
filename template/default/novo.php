<?php

/**
 * The template body
 *
 * @package   Smart Dealer Wordpress Plugin
 * @author    Patrick Otto <patrick@smartdealership.com.br>
 * @author    Jean Carlos dos Santos <jean@smartdealership.com.br>
 * @version   2.5.0
 * @access    public
 * @copyright Smart Dealer(c), Mar 2017-2018
 * @see       http://www.smartdealer.com.br
 */
if (!($row = $api->getRow()) or !(is_array($row)) or !array_key_exists('modelo', $row)) {
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
    'cidade'
), array(), $out);

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
$transmissao = ucwords(\SmartDealer::prepareString($row['transmissao'], true));
$portas = ucwords(\SmartDealer::prepareString($row['portas'], true));
$color = ucwords(\SmartDealer::prepareString($row['cor'], true));
$price = \SmartDealer::preparePrice($row['preco'], true);
?>

<script type="text/javascript">
    stm_lang_code = (typeof stm_lang_code === 'string') ? stm_lang_code : 'pt_br';
    ajaxurl = (typeof ajaxurl === 'string') ? ajaxurl : '<?php echo SmartDealer::formAction() ?>';

    STMListings = (typeof STMListings === 'object') ? STMListings : {
    Filter: {
        prototype: {
            ajaxBefore: function() {},
            ajaxSuccess: function() {}
        }
    }
    };

    });
</script>

<div class="sd-scope">
    <section class="padding-bottom:20px;padding-top: 20px;" style="width:auto;height:auto;display:block;">
        <div class=container>

            <!-- status -->

            <?php if ($out) : ?>

                <div class="alert alert-success" role="alert">Sucesso! Em breve um de nossos vendedores entrará em contato.</div>

            <?php elseif ($out === false) : ?>

                <div class="alert alert-warning" role="alert">Ops! Ocorreu um erro ao enviar as informações.</div>

            <?php endif; ?>

            <div class=row>

                <div class="col-md-12">
                    <div class="col-md-12" style="padding-bottom:10px;">
                        <?php $title = \SmartDealer::prepareString($row['modelo'], true) ?>
                        <h2 class="title" page-meta="title" meta-data="<?php echo $title; ?>"><?php echo $title; ?></h2>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="col-md-12">
                        <div style="max-height: 600px;overflow:hidden">
                            <img class=img-responsive src=<?php echo SmartDealer::img($row['id'], $img[0], $img[1], 640, $i) ?>>
                        </div>
                        <?php $tot = ($row['tot_imagem'] >= 1) ? range(1, $row['tot_imagem']) : array(); ?>
                        <?php if ($tot) : ?>
                            <div id="box-images">
                                <?php foreach ($tot as $i) : ?>
                                    <div class="item">
                                        <div class="item-img">
                                            <a href="<?php echo SmartDealer::img($row['id'], $img[0], $img[1], 640, $i) ?>" data-toggle="lightbox" data-gallery="multiimages" data-title="<?php echo $title ?>">
                                                <img class=" img-responsive" src="<?php echo SmartDealer::img($row['id'], $img[0], $img[1], 300, $i) ?>" alt="<?php echo $i ?>" />
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-4 form-proposer">
                    <form action="<?php echo SmartDealer::formAction(); ?>" method=post class="form-captcha" onsubmit="this.submit.disabled = true; this.submit.value = 'Enviando..';">
                        <div class="col-sm-12">
                            <h3 style="color: #838383">Envie uma proposta</h3>
                        </div>
                        <div class="col-lg-12">
                            <div class=form-group>
                                <label for=InputName>Nome Completo</label>
                                <div class=input-group>
                                    <input type=text class=form-control name=nome id=InputName placeholder="Nome Completo" required>
                                    <span class=input-group-addon><i class="glyphicon glyphicon-ok"></i></span></div>
                            </div>
                            <div class=form-group>
                                <label for=InputEmail>E-mail</label>
                                <div class=input-group>
                                    <input type=email class=form-control id=InputEmail name=email placeholder="E-mail" required>
                                    <span class=input-group-addon><i class="glyphicon glyphicon-ok"></i></span></div>
                            </div>
                            <div class=form-group>
                                <label for=Inputtel>Telefone</label>
                                <div class=input-group>
                                    <input type=text class=form-control name=telefone id=InputName placeholder="Telefone" required>
                                    <span class=input-group-addon><i class="glyphicon glyphicon-ok"></i></span></div>
                            </div>
                            <div class=form-group>
                                <label for=Inputcity>Cidade</label>
                                <div class=input-group>
                                    <input type=text class=form-control name=cidade id=InputName placeholder="Cidade">
                                    <span class=input-group-addon><i class="glyphicon glyphicon-ok"></i></span></div>
                            </div>
                            <div class=form-group>
                                <label for=InputMessage>Mensagem</label>
                                <div class=input-group>
                                    <textarea name=mensagem id=InputMessage class=form-control rows=5 required></textarea>
                                    <span class=input-group-addon><i class="glyphicon glyphicon-ok form-control-feedback"></i></span></div>
                            </div>

                            <div class="clear clearfix"></div>

                            <input type=submit name=submit id=submit value="Enviar proposta" class="btn pull-right btn-primary btn--decorated product__btn">
                            <input type=hidden name=modal value="<?php echo SmartDealer::encodeData('novos'); ?>">
                            <input type=hidden name=id value="<?php echo SmartDealer::encodeData($row['id']); ?>">
                        </div>
                    </form>
                    <div class="clearfix"></div>
                </div>

            </div>
        </div>
    </section>
    <div class="clearfix"></div>
    <hr style="color: #ddd">
    <div class="clearfix"></div>
    <section style="padding-bottom: 20px;padding-top: 20px;">
        <div class="container text-center">

            <div class="col-xs-12 col-md-4 col-lg-4 col-sm-12">

                <h4 class="detail-title">Características</h4>

                <div class="col-lg-12 line-opt">
                    <span class="pull-left" style="font-weight: bold;">KM</span>
                    <span class="pull-right"><?php echo $row['km']; ?></span>
                    <div class="clear clearfix"></div>
                </div>

                <div class="col-lg-12 line-opt">
                    <span class="pull-left" style="font-weight: bold;">Ano/Modelo</span>
                    <span class="pull-right"><?php echo $year ?></span>
                    <div class="clear clearfix"></div>
                </div>

                <div class="col-lg-12 line-opt">
                    <span class="pull-left" style="font-weight: bold;">Combustível</span>
                    <span class="pull-right"><?php echo $fuel ?></span>
                    <div class="clear clearfix"></div>
                </div>

                <div class="col-lg-12 line-opt">
                    <span class="pull-left" style="font-weight: bold;">Transmissão</span>
                    <span class="pull-right"><?php echo $transmissao ?></span>
                    <div class="clear clearfix"></div>
                </div>

                <div class="col-lg-12 line-opt">
                    <span class="pull-left" style="font-weight: bold;">N° de portas</span>
                    <span class="pull-right"><?php echo $portas ?></span>
                    <div class="clear clearfix"></div>
                </div>

                <div class="col-lg-12 line-opt">
                    <span class="pull-left" style="font-weight: bold;">Cor</span>
                    <span class="pull-right"><?php echo $color ?></span>
                    <div class="clear clearfix"></div>
                </div>

            </div>

            <div class="col-xs-12 col-md-4 col-lg-4 col-sm-12">

                <h4 class="detail-title">Itens do Veículo</h4>
                <?php $opt = array_filter((array) (explode(';', trim($row['opcionais'], '; ')))) ?>
                <?php if ($opt) : ?>
                    <ul style="list-style-type:none;">
                        <?php foreach ($opt as $op) : ?>
                            <li style="margin-bottom: 5px;text-align: left;"><i class="glyphicon glyphicon-chevron-right"></i>&nbsp;<?php echo ucwords(SmartDealer::prepareOptString($op)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p>Não informado</p>
                <?php endif; ?>
            </div>

            <div class="col-xs-12 col-md-4 col-lg-4 col-sm-12">

                <h3 style="color: #838383">Por apenas</h3>
                <b class="price-label" style="font-size: 36px;"><?php echo $price; ?></b>
                <p>Oferta com garantia de procedência</p>
            </div>

        </div>
    </section>
</div>

<!-- page styles -->
<link href="<?php echo SmartDealer::url('css/bootstrap.min.css'); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo SmartDealer::url('css/font-awesome.min.css'); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo SmartDealer::url('css/owl.carousel.css'); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo SmartDealer::url('css/owl.theme.css'); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo SmartDealer::url('css/ekko-lightbox.min.css'); ?>" rel="stylesheet" type="text/css" />

<!-- page scripts -->
<script type="text/javascript" src="<?php echo SmartDealer::url('js/owl.carousel.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo SmartDealer::url('js/ekko-lightbox.min.js'); ?>"></script>

<script type="text/javascript">
    SmartDealer.setComplete(function(j) {

        j("#box-images").owlCarousel({
            autoPlay: 3000,
            items: 4,
            itemsDesktop: [1199, 3],
            itemsDesktopSmall: [979, 3]
        });

        j(document).delegate('*[data-toggle="lightbox"]', 'click', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox({
                gallery_parent_selector: '#box-images'
            });
        });

    });
</script>

<!-- footer -->
<?php get_footer(); ?>