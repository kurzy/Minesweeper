<?php
    /* If cell_shape, dimensions, or colouring mode are not set, redirect to index.html */
    if (!isset($_POST["cell_shape"]) || !isset($_POST["dimensions"]) || !isset($_POST["colour_mode"])) {
        header("Location: index.html");
        exit();
    }
    $cell_shape = filter_var($_POST["cell_shape"], FILTER_SANITIZE_STRING);
    $colour_mode = filter_var($_POST["colour_mode"], FILTER_SANITIZE_STRING);
    $dimensions = 0;
    try {
        $dimensions = (int) filter_var($_POST["dimensions"], FILTER_SANITIZE_NUMBER_INT);
    }
    catch (Exception $e) {
        // header("Location: index.html");
        echo $dimensions;
        exit();
    }
    // Check the inputs are valid.
    if ($dimensions == "" || $cell_shape == "" || $colour_mode == "" 
    || $dimensions > 26 || $dimensions < 4 
    || !($cell_shape === "Square" || $cell_shape === "Hexagon")
    || !($colour_mode === "True" || $colour_mode === "False")) {
        header("Location: index.html");
        exit();
    }
    



?>

<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $dimensions; ?> x <?php echo $dimensions; ?> <?php echo $cell_shape;?> | Minesweeper</title>
        <meta charset="utf-8">
        <meta name="author" content="Jack Kearsley, July 2017.">
        <meta name="description" content="Minesweeper Game">
        <link rel="stylesheet" type="text/css" href="desktop.css">
        <link rel="shortcut icon" href="Images/favicon.png" type="image/png">
        <!-- Images made by 'Freepik' from www.flaticon.com -->
        <script type="text/javascript">
            var dim = parseInt("<?php echo $dimensions; ?>");
            var cell_shape = "<?php echo $cell_shape; ?>";
            var colour_mode = "<?php echo $colour_mode; ?>" === "True" ? true : false;
        </script>
        <script type="text/javascript" src="js/msweeper.js"></script>
    </head>
    
    <body onload="init()">
        <div id="main-title">
            <a href="index.html">
                <h1>Minesweeper</h1>
            </a>
        </div>
        
        <div id="game-container">
            <div id="game-controls">
                <div id="game-timer">
                    <h3 class="game-btn-h3" id="timer-h3">00:00</h3>
                </div>
                <div id="game-reset-btn" onclick="controls.resetGame()">
                    <h3 class="game-btn-h3">Reset</h3>
                </div>
            </div>

            <div id="game-board">
                <table id="game-table">
                    <tbody id="game-table-body" oncontextmenu="return false;">
                        <!-- Cells are inserted via Javascript. -->
                    </tbody>
                </table>
        
                <div id="message-div">
                    <!-- Winning/Losing message displayed here when game ends. -->
                </div>
            </div>   
        </div>
        <div id="footer">
            <p>Jack Kearsley, 2017.</p>
        </div>         
    </body>
</html>