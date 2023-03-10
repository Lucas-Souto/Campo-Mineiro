<?php
const BOMBS = 10, SQUARE = 9;
const CLICK = 'c', FLAG = 'p';
const C_BOMB = "*", C_FLAG = "P", C_NOFLAG = "O", C_EMPTY = "_";
const WIN = "win", LOOSE = "loose", NONE = "continue";

$currentState = [];
$behindState = [];

function clamp($value, $min, $max) : int
{
    if ($value < $min) return $min;
    else if ($value > $max) return $max;

    return $value;
}

function neighboringBombs($behindState, $x, $y) : int
{
    $count = 0;
    $xStart = $x - 1;
    $yStart = $y - 1;
    $xEnd = $xStart + 3;
    $yEnd = $yStart + 3;
    
    for ($xx = $xStart; $xx < $xEnd; $xx++)
    {
        if ($xx != clamp($xx, 1, SQUARE)) continue;

        for ($yy = $yStart; $yy < $yEnd; $yy++)
        {
            if ($yy != clamp($yy, 1, SQUARE)) continue;

            if ($behindState[$yy][$xx] === C_BOMB) $count++;
        }
    }

    return $count;
}

function initialize(&$currentState, &$behindState)
{
    $currentState = array_fill(1, SQUARE, array_fill(1, SQUARE, C_NOFLAG));
    $behindState = array_fill(1, SQUARE, array_fill(1, SQUARE, C_EMPTY));

    $generatedBombs = 0;
    
    while ($generatedBombs < BOMBS)
    {
        $x = random_int(1, SQUARE);
        $y = random_int(1, SQUARE);

        if ($behindState[$y][$x] != C_BOMB)
        {
            $behindState[$y][$x] = C_BOMB;
            $generatedBombs++;
        }
    }

    for ($x = 1; $x <= SQUARE; $x++)
    {
        for ($y = 1; $y <= SQUARE; $y++)
        {
            if ($behindState[$y][$x] != C_BOMB)
            {
                $neighboring = neighboringBombs($behindState, $x, $y);
            
                $behindState[$y][$x] = $neighboring === 0 ? C_EMPTY : $neighboring;
            }
        }
    }
}

function renderGame($currentState)
{
    echo "******** Campo Mineiro ********\n";
    echo "c = Clicar; p = Bandeira\n";
    echo "Como jogar: [" . CLICK . "/". FLAG ."] em x,y\n";

    $col = "    ";

    for ($i = 1; $i <= SQUARE; $i++) $col = $col . $i . ' | ';

    echo $col . "\n";

    for ($y = 1; $y <= SQUARE; $y++)
    {
        echo $y . " - ";

        for ($x = 1; $x <= SQUARE; $x++) echo $currentState[$y][$x] . ' | ';

        echo "\n";
    }

    echo "\n";
}

function remain($currentState) : int
{
    $remain = 0;

    foreach ($currentState as $line)
    {
        foreach ($line as $column)
        {
            if ($column === C_NOFLAG || $column === C_FLAG) $remain++;
        }
    }

    return $remain;
}

function spread($x, $y, &$currentState, $behindState)
{
    if (!($x > 0 && $x <= SQUARE && $y > 0 && $y <= SQUARE)) return;
    elseif ($currentState[$y][$x] == $behindState[$y][$x]) return;
    
    $currentState[$y][$x] = $behindState[$y][$x];

    if ($behindState[$y][$x] == C_EMPTY)
    {
        spread($x + 1, $y, $currentState, $behindState);
        spread($x - 1, $y, $currentState, $behindState);
        spread($x, $y + 1, $currentState, $behindState);
        spread($x, $y - 1, $currentState, $behindState);
    }
}

function handleInput($input, &$currentState, $behindState) : string
{
    $cmd = explode(" em ", $input);
    $action = $cmd[0];
    $pos = explode(",", $cmd[1]);
    $x = intval($pos[0]);
    $y = intval($pos[1]);

    if ($action == CLICK)
    {
        if ($behindState[$y][$x] == C_BOMB)
        {
            $currentState = $behindState;

            return LOOSE;
        }
        else
        {
            if ($behindState[$y][$x] === C_EMPTY) spread($x, $y, $currentState, $behindState);
            else $currentState[$y][$x] = $behindState[$y][$x];
            
            if (remain($currentState) == BOMBS)
            {
                $currentState = $behindState;

                return WIN;
            }
        }
    }
    elseif ($action == FLAG)
    {
        $currentState[$y][$x] = C_FLAG;

        if (remain($currentState) == BOMBS)
        {
            $currentState = $behindState;

            return WIN;
        }
    }
    else echo "\nBah! Comando errado.\n";

    return NONE;
}

initialize($currentState, $behindState);

while (true)
{
    renderGame($currentState);

    echo "Que queres fazer, s???\n";

    $input = readline(">");
    $result = handleInput($input, $currentState, $behindState);

    if ($result !== NONE)
    {
        renderGame(($currentState));

        if ($result === WIN) echo "\nVoc?? venceu!!! N??o fez mais que a sua obriga????o.";
        else if ($result === LOOSE) echo "\nPerdeu no campo minadokkkkkkkkkkkkkkkkkkkkkkkk.";

        break;
    }
}
?>