<!DOCTYPE html>
<html lang="nl">
    <head>
        <title>Winkelwagen - NerdyGadgets</title>

        <!-- Javascript -->
        <script src="js/jquery.min.js"></script>
        <script src="js/popper.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/resizer.js"></script>

        <!-- Style sheets-->
        <link rel="stylesheet" href="css/main.css" type="text/css">
    </head>
    <body>
        <script>
            $(function () {
                $('[data-toggle="tooltip"]').tooltip()
            })
            $('#example').popover(options)
        </script>
        <?php
            session_start();
            include "header.php";
            include "../src/functions.php";
        ?>

        <?php
        $_SESSION[ 'total'] = 0;
        $_SESSION[ 'noDiscount'] = 0;
        $kortingscode = '';
        $correctDiscount = true;
        if (isset($_POST['korting'])){
            unset($_POST['korting']);
            if (isset($_POST['kortingscode'])) {
                $kortingscode = $_POST['kortingscode'];
                unset($_POST['kortingscode']);
                $_SESSION['korting'] = getDiscountCode($kortingscode, $databaseConnection);
                $_SESSION['korting']['naam'] = $kortingscode;
                if (!isset($_SESSION['korting'][0]['codenaam']) && $kortingscode != ''){
                    $correctDiscount = false;
                }

                if ((!checkCodeDate($_SESSION['korting']['naam'], $databaseConnection)) || (!checkUses($_SESSION['korting']['naam'], $databaseConnection))){
                    unset($_SESSION['korting']);
                    $correctDiscount = false;
                }
            }
        }

        foreach($_POST as $key => $value):
//            if(is_int($value) || is_float($value)):
                $value = abs($value);
                if($value == 0) {
                    continue;
                }
                $stock = getItemStock($key, $databaseConnection);
                $value = ($value <= $stock) ? $value : $stock;
                $_SESSION['cart'][$key] = abs($value);
//            endif;
        endforeach;

        function updateSession($arrayName) {

            if($arrayName == 'cart' || $arrayName == 'registration') {

                foreach($_POST as $key => $value):
                    $value = abs($value);
                    $stock = getItemStock($key, $databaseConnection);
                    $value = ($value <= $stock) ? $value : $stock;
                    $_SESSION[$arrayName][$key] = abs($value);
                endforeach;

            } else {
                return "Function is not capable of handling this request";
            }

            foreach($_POST as $key => $value):
                $value = abs($value);
                $stock = getItemStock($key, $databaseConnection);
                $value = ($value <= $stock) ? $value : $stock;
                $_SESSION[$arrayName][$key] = abs($value);
            endforeach;

        }


        if (!isset($_SESSION['cart'])):
            $_SESSION['cart'] = [];
        endif; ?>

        <section class="shopping-cart">
            <div class="container">
                <div class="row">
                    <div class="col-8">
                        <div class="shopping-cart__cart bg-white bg-white--large">
                            <h1 class="shopping-cart__title">Winkelmandje</h1>

                            <?php
                            if(count($_SESSION['cart']) !== 0): ?>
                                <?php foreach ($_SESSION['cart'] as $key => $item):

                                    $stockItem = getStockItem($key, $databaseConnection);
                                    if (!$stockItem):
                                        continue;
                                    endif;
                                    $quantity = $_SESSION['cart'][$stockItem['StockItemID']];
                                    if($quantity == 0) {
                                        continue;
                                    }
                                    ?>
                                    <div class="card">
                                        <a href="view.php?id=<?= $key ?>" class="card__img">
                                            <?php if($stockItemImage = getStockItemImage($key, $databaseConnection)): ?>
                                                <img class='img-fluid' src="<?= 'img/stock-item/' . $stockItemImage[0]['ImagePath'] ?>">
                                            <?php else: ?>
                                                <img class='img-fluid' src="<?= 'img/stock-item/' . $stockItem['BackupImagePath'] ?>">
                                            <?php endif; ?>
                                        </a>

                                        <div class="card__description">
                                            <div>
                                                <h2><?= $stockItem['StockItemName'] ?></h2>
                                                <div class="card__price">&euro; <?= number_format($stockItem['SellPrice'], 2, '.', ',') ?> <span>Inclusief btw</span></div>
                                            </div>
                                            <span class="card__stock">
                                                Artikelnummer: <?= $stockItem['StockItemID'] ?>
                                            </span>
                                        </div>

                                        <div class="card__count">
                                            <form method='post'>
                                                <?php
                                                $stock = getItemStock($stockItem['StockItemID'], $databaseConnection);
                                                $price = round($stockItem['SellPrice'], 2);
                                                $factor = 1;
                                                if (isset($_SESSION['korting'][0]['procent'])){
                                                    $factor = (1 - ($_SESSION['korting'][0]['procent'] * 0.01));
                                                }
                                                $_SESSION['total'] += ($price * $factor) * $quantity;
                                                $_SESSION[ 'noDiscount'] += $price * $quantity; ?>

                                                <input class="btn" name="<?= $stockItem['StockItemID'] ?>" onchange="this.form.submit()" min="1" type="number" value="<?= $quantity ?>" max="<?= $stock ?>">

                                            </form>

                                            <div class="close">
                                                <a href="<?= 'cart.php?remove=' . $key ?>" class="text-danger">&#10005; </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                            <?php else: ?>
                                <h2>Uw winkelmandje is leeg.</h2>
                                <a style="color: #007bff" href="browse.php">Bladeren door producten...</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="shopping-cart__checkout bg-white">
                            <?php if (empty($_SESSION['cart'])): ?>
                                <h2>Uw winkelmandje is leeg.</h2>
                            <?php else: ?>
                                <h5>
                                    <b>Overzicht</b>
                                </h5>
                                <hr>
                                <div class="shopping-cart__total">
                                    <?php if (isset($_SESSION['korting'][0]['procent'])): ?>
                                        <div class="">Prijs (<?= ($_SESSION['korting'][0]['procent'] . "% korting")?>)</div>
                                        <div class=" text-right"><s>&euro; <?= (number_format($_SESSION['noDiscount'], 2, '.', ',')) ?></s> &euro; <?= (number_format(($_SESSION[ 'total']), 2, '.', ',')) ?></div>
                                    <?php else: ?>
                                        <div class="">Prijs</div>
                                        <div class=" text-right">&euro; <?= (number_format($_SESSION['noDiscount'], 2, '.', ',')) ?></div>
                                    <?php endif; ?>
                                    <?php
                                    if ($_SESSION['total'] < getDeliverycosts($databaseConnection)[0][1]):
                                        $deliveryCosts = getDeliverycosts($databaseConnection)[1][1];
                                    else: $deliveryCosts = 0;
                                    endif;
                                    $_SESSION['deliveryCosts'] = $deliveryCosts;
                                    $_SESSION['total'] += $_SESSION['deliveryCosts'];
                                    ?>
                                    <div class="">Verzendkosten
                                        <a style="text-decoration: underline dotted;" href="#" data-toggle="popover" data-content="<?= ("Gratis verzend kosten vanaf €" . getDeliverycosts($databaseConnection)[0][1]) ?>">?</a>
                                    </div>
                                    <div class="text-right" style="margin-left: auto; margin-right: 0;">&euro; <?= (number_format($deliveryCosts, 2, '.', ',')) ?></div>
                                </div>
                                <hr>
                                <div class="shopping-cart__total">
                                    <div class="">Totaal</div>
                                    <div class=" text-right">&euro; <?= (number_format($_SESSION['total'], 2, '.', ',')) ?></div>
                                </div>
                                <hr>
                                <div>
                                    <form class="shopping-cart__form" method="post" action="">
<!--                                        <label for="kortingscode">Kortingscode:</label>-->
                                        <?php
                                        $value = '';
                                        if (isset($_SESSION['korting'][0]['procent'])):
                                            $value = $_SESSION['korting']['naam'];
                                        endif; ?>
                                        <input class="input" id="kortingscode" type="text" placeholder="kortingscode" name="kortingscode" value="<?= $value ?>">
                                        <input class="btn--primary" id="kortingscodeInput" type="submit" value="Bevestig" name="korting">
                                    </form>
                                    <div class="text-warning">
                                        <?php
                                        if (!$correctDiscount) {
                                            print("Ongeldige kortingscode! Probeer opnieuw.");
                                        }
                                        ?></div>
                                </div>
                                <hr>
                                <div class="text-right">
                                    <a href="./order.php" class="btn btn--order">Bestelling plaatsen</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php include "footer.php"; ?>

        <script>
            $(document).ready(function(){
                $('[data-toggle="popover"]').popover();
            });
            if ( window.history.replaceState ) {
                window.history.replaceState( null, null, window.location.href );
            }
            // Verwijder URL GET-query om dubbele uitvoering te voorkomen op ververs.
            // window.history.pushState("object or string", "Title", "/" + window.location.href.substring(window.location.href.lastIndexOf('/') + 1).split("?")[0]);
        </script>

    </body>
</html>
