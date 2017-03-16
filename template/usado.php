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
if (!($row = $api->getRow()) or ! (is_array($row)) or ! array_key_exists('modelo', $row))
    SmartDealer::show_404();

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
# Dynamic data
# - - - - - - - - - - - - - - -

$year = explode('/', $row['ano_fab_mod']);
$year = (\SmartDealer::prepareYear(current($year)) . '/' . \SmartDealer::prepareYear(next($year)));
$fuel = \SmartDealer::prepareFuel($row['combustivel']);
$color = ucwords(\SmartDealer::prepareString($row['cor'], true));
$price = \SmartDealer::preparePrice($row['preco'], true);
?>
<div class="sd-scope">
    <section class="sd-container">
        <?php if ($out): ?>
            <div class="alert alert-success" role="alert">Sucesso! Um de nossos vendedores entrará em contato.</div>
        <?php elseif ($out === false): ?>
            <div class="alert alert-warning" role="alert">Ops! Ocorreu um erro ao enviar as informações.</div>
        <?php endif; ?>
        <div class="col-md-12">
            <div class="col-md-12">
                <?php $title = \SmartDealer::prepareText($row['modelo'], 1) ?>
                <h1 page-meta="title" meta-data="<?php echo $title; ?>">
                    <?php echo $title; ?>
                </h1>
                <p page-meta="description" meta-data="<?php echo $title . ' por ' . $price; ?>"></p>
            </div>
        </div>
        <div class="col-md-8">
            <div class="col-md-12">
                <div style="max-height: 600px;overflow:hidden">
                    <img class=img-responsive src="<?php echo SmartDealer::img($row['id'], null, null, 640, 1, true) ?>">
                </div>
                <?php $tot = ($row['img_t'] >= 1) ? range(1, (int) $row['img_t']) : array(1); ?>
                <?php if ($tot): ?>
                    <div id="box-images">
                        <?php foreach ($tot as $i): ?>
                            <div class="item">
                                <div class="item-img">
                                    <a href="<?php echo SmartDealer::img($row['id'], null, null, 640, $i, true) ?>" data-toggle="lightbox" data-gallery="multiimages" data-title="<?php echo $title ?>">
                                        <img src="<?php echo SmartDealer::img($row['id'], null, null, 180, $i, true) ?>" alt="<?php $i ?>"/>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="clear clearfix"></div>
        </div>
        <div class="col-md-4">
            <form action="<?php echo SmartDealer::formAction(); ?>" id="form-lead" method="post">
                <input type=hidden name=id  value="<?php echo SmartDealer::encodeData($row['id']); ?>">
                <h4>Envie uma proposta</h4>
                <div class=form-group>
                    <input type=text class=form-control name=nome placeholder="Nome Completo" required>
                </div>
                <div class=form-group>
                    <input type=email class=form-control name=email placeholder="E-mail" required>
                </div>
                <div class=form-group>
                    <input type=text class=form-control name=telefone data-mask="phone" placeholder="Telefone" required>
                </div>
                <div class=form-group>
                    <input type=text class=form-control name=cidade placeholder="Cidade">
                </div>
                <div class=form-group>
                    <input type=text class=form-control name=estado data-mask="uf" id=InputName placeholder="Estado">
                </div>
                <div class=form-group>
                    <textarea name=mensagem class=form-control rows=5 placeholder="Mensagem ao vendedor" required></textarea>
                </div>
                <div class=form-group>
                    <input type=submit name=submit id=submit value="Enviar proposta" class="btn pull-right btn-primary">
                </div>
                <div class="clear clearfix"></div>
            </form>
        </div>   
    </section>
    <section class="sd-container">
        <div class="col-md-8">
            <h4>Características</h4>
            <table class="table">
                <tbody>
                    <tr>
                        <th scope="row">KM</th>
                        <td><?php echo $row['km']; ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Ano/Modelo</th>
                        <td><?php echo $year; ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Combustível</th>
                        <td><?php echo $fuel; ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Cor</th>
                        <td><?php echo $color; ?></td>
                    </tr>
                </tbody>
            </table>
            <div class="clear clearfix"></div>
        </div>
        <div class="col-md-4 col-price">
            <h4>&nbsp;</h4>
            <div class="label-price">
                <h4>Por apenas </h4>
                <span><?php echo $price; ?></span>
            </div>
            <div class="clear clearfix"></div>
        </div>
        <div class="col-md-6">
            <h4>Observações</h4>
            <span><?php echo html_entity_decode($row['obs_externa']) ?></span>
            <div class="clear clearfix"></div>
        </div>
        <div class="col-md-6">
            <h4>Opcionais</h4>
            <?php $opt = array_filter((array) (explode(';', trim($row['opcionais'], '; ')))) ?>
            <?php if ($opt) : ?>
                <ul style="list-style-type:none;padding: 0;font-style:normal;">
                    <?php foreach ($opt as $op): ?> 
                        <li style="margin-bottom: 5px;"><i class="glyphicon glyphicon-chevron-right"></i>&nbsp;<?php echo ucwords(SmartDealer::prepareOptString($op)) ?></li>
                    <?php endforeach; ?>
                </ul>  
            <?php else: ?>
                <p>Não informados</p>
            <?php endif; ?>
            <div class="clear clearfix"></div>
        </div>
    </section>
    <div class="clear clearfix"></div>
</div>

<!-- page styles -->
<link href="<?php echo SmartDealer::url('css/jquery-ui.min.css'); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo SmartDealer::url('css/bootstrap-minimal.css'); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo SmartDealer::url('css/bootstrap-social.css'); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo SmartDealer::url('css/font-awesome.min.css'); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo SmartDealer::url('css/owl.carousel.css'); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo SmartDealer::url('css/owl.theme.css'); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo SmartDealer::url('css/ekko-lightbox.min.css'); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo SmartDealer::url('css/tpl-style.css'); ?>" rel="stylesheet" type="text/css"/>

<!-- page scripts -->
<script type="text/javascript" src="<?php echo SmartDealer::url('js/jquery-ui.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo SmartDealer::url('js/owl.carousel.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo SmartDealer::url('js/ekko-lightbox.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo SmartDealer::url('js/jquery.mask.js'); ?>"></script>
<script type="text/javascript" src="<?php echo SmartDealer::url('js/jquery.validate.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo SmartDealer::url('js/detalhes.js'); ?>"></script>