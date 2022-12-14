<?php
    include_once __DIR__ . '/../../src/environment.php';
    $total = 0;
//    if(count($_POST) > 0):
//        $_SESSION['userinfo'] = $_POST;
//    endif;
    $databaseConnection = $GLOBALS['databaseConnection'];
    $dateformatter = new IntlDateFormatter(
        'nl_NL',
        IntlDateFormatter::FULL,
        IntlDateFormatter::FULL,
        'Europe/Amsterdam',
        IntlDateFormatter::GREGORIAN,
        'EEEE d MMMM'
    );
    $shippingTime = '1 day';
    $shippingDate = $dateformatter->format(strtotime("+$shippingTime", mktime(0, 0, 0)));
?>
<section class="checkout">
    <div class="container">
        <div class="row">
            <div class="col-4">
                <div class="checkout__wrapper bg-white">
                    <h5 class="checkout__title">Ingevulde gegevens</h5>

                    <?php
                        $firstname = $_SESSION['userinfo']['firstname'];
                        $prefixName = $_SESSION['userinfo']['prefixName'];
                        $surname = $_SESSION['userinfo']['surname'];

                        $postalzip = $_SESSION['userinfo']['postcode'];
                        $city = $_SESSION['userinfo']['city'];
                        $adress = $_SESSION['userinfo']['street'] . ' ' . $_SESSION['userinfo']['housenumber'];
                    ?>

                    <ul>
                        <li><?= $firstname . " $prefixName" . " $surname" ?></li>
                        <li><?= $_SESSION['userinfo']['email'] ?></li>
                        <?php if (isset($_SESSION['userinfo']['phone'])): ?>
                            <li><?= $_SESSION['userinfo']['phone'] ?></li>
                        <?php endif; ?>
                    </ul>

                    <ul>
                        <li><?= $postalzip . ", " . $city  ?></li>
                        <li><?= $adress  ?></li>
                    </ul>

                    <span>Uitzonderingen</span>
                    <?php if (isset($_SESSION['userinfo']['comment'])): ?>
                        <p><?= $_SESSION['userinfo']['comment'] ?></p>
                    <?php else: ?>
                        <p>---</p>
                    <?php endif; ?>

                    <h5 class="checkout__title checkout__title-delivery">Bezorgmoment</h5>
                    <ul>
                        <li>
                            <?= ucfirst($shippingDate) ?>
                        </li>
                        <li>PostNL</li>
                    </ul>
                </div>
            </div>
            <div class="col-8">
                <div class="checkout__wrapper bg-white bg-white--large">
                    <h2 style="padding-bottom: 18px">Te bestellen producten</h2>

                    <div class="checkout__products">
                        <?php
                        $total = 0;
                        $factor = 1;
                        foreach($_SESSION['cart'] as $id => $quantity): ?>
                            <?php $stockItem = getStockItem($id, $GLOBALS['databaseConnection']);
                            $price = round($stockItem['SellPrice'], 2);
                            if (isset($_SESSION['korting'][0]['procent'])){
                                $factor = (1 - ($_SESSION['korting'][0]['procent'] * 0.01));
                            }
                            ?>
                            <div class="container d-flex align-items-center">
                                <div class="row w-100">
                                    <div class="col-6 display align-middle text-left w-100">
                                        <label for="quantity" class=""><?=$stockItem['StockItemName']?></label>
                                    </div>
                                    <div class="col-2 text-right align-middle">
                                        <p>Aantal: <?=$quantity?></p>
                                    </div>
                                    <div class="col-2 text-right">
                                        <?php if (isset($_SESSION['korting'][0]['procent'])): ?>
                                        <del style="display:inline-block;">&euro;<?=number_format($price * $quantity, 2)?></del>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-2 text-left">
                                        <p style="display:inline-block;">&euro;<?=number_format($price * $factor * $quantity, 2)?></p>

                                    </div>
                                </div>

                            </div>
                        <?php endforeach; ?>

                        <hr>
                        <div class="container d-flex align-items-center">
                            <div class="row w-100">
                                <div class="col-10  display align-middle text-right w-100">
                                    <p style="display: inline-block">
                                        Verzendkosten:
                                    </p>
                                </div>
                                <div class="col-2 text-left">
                                    <p style="display: inline-block">
                                        &euro;<?= number_format($_SESSION['deliveryCosts'],2) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="container d-flex align-items-center">
                            <div class="row w-100">
                                <div class="col-10  display align-middle text-right w-100">
                                    <p style="display: inline-block">
                                        Totaal:
                                    </p>
                                </div>
                                <div class="col-2 text-left">
                                    <p style="display: inline-block">
                                        &euro;<?= number_format($_SESSION['total'],2) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <a href="?action=pay" class="btn btn--order">Ga naar betalen</a>
                    <?php if(isset($_GET['action']) && $_GET['action'] == 'pay' && strtolower(getEnvironmentVariable('CHECKOUT_ENABLED') == 'true')):

                        $userID = NULL;
                        if(isset($_SESSION['isLoggedIn']) && $_SESSION['isLoggedIn']):
                            $userID = $_SESSION['account']['id'];
                        endif;
                        $bool = processOrder($userID, $databaseConnectionWriteAccess);

                        if($bool):
                            $_SESSION['userinfo'] = '';
                            $_SESSION['cart'] = []; ?>
                            <script>
                                window.location.replace('https://www.ideal.nl/demo/qr/?app=ideal');
                            </script>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>