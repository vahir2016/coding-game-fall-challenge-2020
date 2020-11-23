<?php

// @codingStandardsIgnoreStart
/**
 * Auto-generated code below aims at helping you parse
 * the standard input according to the problem statement.
 **/

// DEBUG

function debug($var, $title = '')
{
    // To debug: error_log(var_export($var, true)); (equivalent to var_dump)
    error_log(var_export($title, true));
    error_log(var_export($var, true));
}

// DO ACTION

function action($type, $id = '')
{
    echo($type." ".$id." \n");

    return $type;
}

// INIT

function getActionCount()
{
    // $actionCount: the number of spells and recipes in play
    fscanf(STDIN, "%d", $actionCount);

    return $actionCount;
}

function getActions($actionCount)
{
    $actions = [];

    for ($i = 0; $i < $actionCount; $i++) {
        // $actionId: the unique ID of this spell or recipe
        // $actionType: in the first league: BREW; later: CAST, OPPONENT_CAST, LEARN, BREW
        // $delta0: tier-0 ingredient change
        // $delta1: tier-1 ingredient change
        // $delta2: tier-2 ingredient change
        // $delta3: tier-3 ingredient change
        // $price: the price in rupees if this is a potion
        // $tomeIndex: in the first two leagues: always 0; later: the index in the tome if this is a tome spell, equal to the read-ahead tax; For brews, this is the value of the current urgency bonus
        // $taxCount: in the first two leagues: always 0; later: the amount of taxed tier-0 ingredients you gain from learning this spell; For brews, this is how many times you can still gain an urgency bonus
        // $castable: in the first league: always 0; later: 1 if this is a castable player spell
        // $repeatable: for the first two leagues: always 0; later: 1 if this is a repeatable player spell
        fscanf(
            STDIN,
            "%d %s %d %d %d %d %d %d %d %d %d",
            $actionId,
            $actionType,
            $delta0,
            $delta1,
            $delta2,
            $delta3,
            $price,
            $tomeIndex,
            $taxCount,
            $castable,
            $repeatable
        );

        $actions[$actionType][] = [
            'actionId' => $actionId,
            'actionType' => $actionType,
            'delta0' => $delta0,
            'delta1' => $delta1,
            'delta2' => $delta2,
            'delta3' => $delta3,
            'price' => $price,
            'tomeIndex' => $tomeIndex,
            'taxCount' => $taxCount,
            'castable' => $castable,
            'repeatable' => $repeatable,
        ];
    }

    return $actions;
}

function getPlayersInformations()
{
    $players = [];

    for ($i = 0; $i < 2; $i++) {
        // $inv0: tier-0 ingredients in inventory
        // $score: amount of rupees
        fscanf(STDIN, "%d %d %d %d %d", $inv0, $inv1, $inv2, $inv3, $score);

        $players[$i] = [
            'inv0' => $inv0,
            'inv1' => $inv1,
            'inv2' => $inv2,
            'inv3' => $inv3,
            'score' => $score,
        ];
    }

    return $players;
}

function getBrews($actions)
{
    return $actions['BREW'];
}

function getCasts($actions)
{
    return $actions['CAST'];
}

function getOpponentCast($actions)
{
    return $actions['OPPONENT_CAST'];

}

function getLearns($actions)
{
    return $actions['LEARN'];
}


// FOCUS BREW

function calculateBrewValue($brew)
{
    return $brew['price'];
//    $price = $brew['price'];
//    $cost = 1 + $brew['delta0'] * 1 + $brew['delta1'] * 2 + $brew['delta2'] * 3 + $brew['delta3'] * 4;
//    $value = $price / $cost;
//
//    return $value;
}

function calculateBrewDistance($brew, $player)
{
    if ($brew['delta0'] == 0) {
        $d0 = 0;
    } else {
        $d0 = max0($player['inv0'] + $brew['delta0']);
    }

    if ($brew['delta1'] == 0) {
        $d1 = 0;
    } else {
        $d1 = max0($player['inv1'] + $brew['delta1']) * 2;
    }

    if ($brew['delta2'] == 0) {
        $d2 = 0;
    } else {
        $d2 = max0($player['inv2'] + $brew['delta2']) * 3;
    }

    if ($brew['delta3'] == 0) {
        $d3 = 0;
    } else {
        $d3 = max0($player['inv3'] + $brew['delta3']) * 4;
    }

    return $d0 + $d1 + $d2 + $d3;
}

function getClosestBestValueBrew($brews, $player, $round)
{
    $r = [];

    foreach ($brews as $brew) {
        $distance = calculateBrewDistance($brew, $player);
        $value = calculateBrewValue($brew);

        $r[] = [
            'distance' => $distance,
            'value' => $value,
            'brew' => $brew,
        ];
    }

    if ($round < 600) {
        array_multisort(
            array_column($r, 'distance'),
            SORT_DESC,
            array_column($r, 'value'),
            SORT_DESC,
            $r
        );
    } else {
        array_multisort(
            array_column($r, 'value'),
            SORT_DESC,
            array_column($r, 'distance'),
            SORT_DESC,
            $r
        );
    }


    $closestBrew = $r[0]['brew'];

    return $closestBrew;
}

function getFocusBrew($brews, $player, $round)
{
    $focusAction = getClosestBestValueBrew($brews, $player, $round);

    return $focusAction;
}


// CAN MAKE ACTION

function canMakeAction($inventory, $action)
{
    return isCastable($action) && hasEnoughtIngredients($inventory, $action);
}

function canMakeActionAfterRest($inventory, $action)
{
    return !isCastable($action) && hasEnoughtIngredients($inventory, $action);
}

function isCastable($action)
{
    return $action['castable'] == 1;
}

function hasEnoughtIngredients($inventory, $action)
{
    for ($i = 0; $i < 4; $i++) {

        $cost = ($inventory['inv'.$i] + $action['delta'.$i]);


        if ($cost < 0) {
            return false;
        }
    }

    return true;
}

function canRest($casts)
{
    foreach ($casts as $cast) {
        if (!isCastable($cast)) {
            return true;
        }
    }

    return false;
}


// CRAFT INGREDIENTS

function isInventoryFull($player)
{
    $isInventoryFull = false;
    $total = 0;
    for ($i = 0; $i < 4; $i++) {
        $total += $player['inv'.$i];
    }

    if ($total > 10) {
        $isInventoryFull = true;
    }

    return $isInventoryFull;
}

function getPlayerInventoryAfterCast($player, $cast)
{
    $player['inv0'] = $player['inv0'] + $cast['delta0'];
    $player['inv1'] = $player['inv1'] + $cast['delta1'];
    $player['inv2'] = $player['inv2'] + $cast['delta2'];
    $player['inv3'] = $player['inv3'] + $cast['delta3'];

    return $player;
}


function getCastForIngredient($ingredientId, $casts)
{
    foreach ($casts as $cast) {
        if ($cast['delta'.$ingredientId] > 0) {
            return $cast;
        }
    }

    return null;
}

function getMissingIngredient($player, $brew)
{
    return [
        $player['inv0'] + $brew['delta0'],
        $player['inv1'] + $brew['delta1'],
        $player['inv2'] + $brew['delta2'],
        $player['inv3'] + $brew['delta3'],
    ];
}

function getGain($missingIngredients, $cast)
{
    return [
        $missingIngredients[0] + $cast['delta0'],
        $missingIngredients[1] + $cast['delta1'],
        $missingIngredients[2] + $cast['delta2'],
        $missingIngredients[3] + $cast['delta3'],
    ];
}

function max0($g)
{
    if ($g > 0) {
        return 0;
    }

    return $g;
}

function getValue($gain)
{
    return max0($gain[0]) + max0($gain[1]) * 2 + max0($gain[2]) * 3 + max0($gain[3]) * 4;
}

function getBestBetweenSameValue($brews)
{
    $values = [];

    foreach ($brews as $brew) {
//        $v = $brew['delta0'] + $brew['delta1'] * 2 + $brew['delta2'] * 3 + $brew['delta3'] * 4;
//        $values[$v] = $brew;
        $values[$brew['actionId']] = $brew;
    }

    ksort($values);
    $bestBrew = array_shift($values);

    return $bestBrew;
}

function craftMissingIngredientsForBrew($player, $brew, $casts)
{
    // on regarde les ingrédients manquants pour faire le sort cible
    $missingIngredients = getMissingIngredient($player, $brew);

    $action = 'WAIT';
    $actionId = '';

    // on calcule les gains de chaque sort
    $gains = [];
    foreach ($casts as $cast) {

        $gain = getGain($missingIngredients, $cast);

        $gains[$cast['actionId']] = [
            'invFull' => isInventoryFull(getPlayerInventoryAfterCast($player, $cast)),
            'castable' => isCastable($cast),
            'gain' => $gain,
            'value' => getValue($gain),
            'canMake' => canMakeAction($player, $cast),
            'hasIng' => hasEnoughtIngredients($player, $cast),
            'cast' => $cast,
        ];
    }

    usort(
        $gains,
        function ($a, $b) {
            return $b['value'] <=> $a['value'];
        }
    );

    // si on a un sort qui permet de finir à ce tour ci ou au tour suivant
    foreach ($gains as $gain) {
        if ($gain['value'] == 0 && !$gain['invFull']) {
            if ($gain['castable']) {
                $action = 'CAST';
                $actionId = $gain['cast']['actionId'];
            } else {
                $action = 'REST';
            }
        }
    }

    if ($action == 'WAIT') {
        // 1er tour on regarde si on a un sort castable utile et jouable
        $continue = true;
        foreach ($gains as $gain) {
            if ($gain['castable']
                && !$gain['invFull']
                && $gain['canMake']
            ) {
                $action = 'CAST';
                $actionId = $gain['cast']['actionId'];
                $value = $gain['value'];
                $continue = false;
                break;
            }
        }

        if (!$continue) {
            $couldBeDoneCast = [];
            foreach ($gains as $gain) {
                if ($gain['value'] == $value
                    && $gain['castable']
                    && !$gain['invFull']
                    && $gain['canMake']) {
                    $couldBeDoneCast[] = $gain['cast'];
                }
            }

            $bestBrew = getBestBetweenSameValue($couldBeDoneCast);
            $actionId = $bestBrew['actionId'];
        }

        if ($continue) {
            // 2ème tour on regarde si on a sort REST utile
            foreach ($gains as $gain) {
                if (!$gain['castable']
                    && !$gain['invFull']
                    && $gain['hasIng']
                ) {
                    $action = 'REST';
                    $actionId = '';
                    break;
                }
            }
        }
    }

    action($action, $actionId);

    return $action;
}


// LEARN
function learnCast($learns, &$totalLearn)
{
    return action("LEARN", $learns[$totalLearn]['actionId']);
}


// DECIDE
function decide($brews, $players, $casts, $round)
{
    // GET BREW FOCUSED
    $focusBrewAction = getFocusBrew($brews, $players[0], $round);

    debug($focusBrewAction['actionId'], 'focus brew ActionId');

    // can brew focused potion ?
    if (hasEnoughtIngredients($players[0], $focusBrewAction)) {
        // yes ? brew it
        $action = action('BREW', $focusBrewAction['actionId']);
    } else {
        $action = craftMissingIngredientsForBrew($players[0], $focusBrewAction, $casts);
    }

    return $action;
}


// GAME LOOP
$round = 0;
$totalLearn = 0;
$lastAction = null;
while (true) {

    // init
    $round++;
    $actionCount = getActionCount();
    $actions = getActions($actionCount);
    $players = getPlayersInformations();
    $casts = getCasts($actions);
    $opponentCasts = getOpponentCast($actions);
    $brews = getBrews($actions);
    $learns = getLearns($actions);

    // pour éviter une boucle infinie de WAIT
    if ($lastAction == 'WAIT') {
        if (canRest($casts)) {
            $lastAction = action('REST');
        } else {
            $lastAction = learnCast($learns, $totalLearn);
        }
    } else {
        if ($round < 8) {
            $lastAction = learnCast($learns, $totalLearn);
        } else {
            if ($round < 20 and $lastAction == 'BREW') {
                $lastAction = learnCast($learns, $totalLearn);
            } else {
                $lastAction = decide($brews, $players, $casts, $round);
            }

        }
    }
}


