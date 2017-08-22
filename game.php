<?php
    /* If cell_shape, dimensions, or colouring mode are not set, redirect to index.html */
    if (!isset($_POST["cell_shape"]) || !isset($_POST["dimensions"]) || !isset($_POST["colour_mode"])) {
        header("Location: index.html");
        exit();
    }
    $cell_shape = $_POST["cell_shape"];
    $dimensions = filter_var($_POST["dimensions"], FILTER_SANITIZE_STRING);
    $colour_mode = $_POST["colour_mode"];
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
            // Get values from index.html POST.
            var cell_shape = "<?php echo $cell_shape; ?>";
            var dim = parseInt("<?php echo $dimensions; ?>");
            var colour_mode = "<?php echo $colour_mode; ?>" === "True" ? true : false;
            
            // Cell constructor.
            function cell() {
                this.covered = true;
                this.flagged = false;
                this.number = 0;
                this.bomb = false;
            }
            
            // Set up multidimensional array of cells.
            var board = new Array(dim);
            function resetBoard() {
                for (var i = 0; i < dim; i++) {
                    board[i] = new Array(dim);
                    for (var j = 0; j < dim; j++) {
                        board[i][j] = new cell();
                    }
                }
            }
            
            var interval;
            var stopwatch_running = false;
            function newGame() {
                time_box = document.getElementById("timer-h3");
                time_box.innerHTML = "00:00";
                
                resetBoard();
                                
                var tbl = document.getElementById("game-table-body");
                
                // Insert HTML cells into the table.
                for (var i = 0; i < dim; i++) {
                    var row = tbl.insertRow(i);
                    row.setAttribute("id", "row" + i );
                    for (var j = 0; j < dim; j++) {
                        var cell = row.insertCell(j);
                        var innerDiv = document.createElement("div");
                        innerDiv.setAttribute("id", "col" + j);
                        innerDiv.setAttribute("onmousedown", "detectClick(event, this)");
                        innerDiv.innerHTML = "&nbsp";
                        if (cell_shape == "Square") {
                            innerDiv.setAttribute("class", "square-cell");
                            cell.appendChild(innerDiv);
                        }
                        else if (cell_shape == "Hexagon") {
                           
                            innerDiv.setAttribute("class", "hexagon-cell-inner");
                            cell.appendChild(innerDiv);
                            cell.setAttribute("class", "hex-outer");
                            tbl.parentElement.setAttribute("style", tbl.parentElement.getAttribute("style") + " border: none;");
                        }
                    }
                    // If hexagon cells, apply formatting to every 2nd row.
                    if (cell_shape == "Hexagon") {
                        var x = 0;
                        if (i % 2 == 1) {
                            row.setAttribute("class", row.getAttribute('class') + " skewed");
                            x = 12;
                        }
                        if (i > 0) {
                            var y = i * 10;
                            row.setAttribute("style", 'transform: translate(' + x + 'px, ' + (-y) + 'px)');
                        }
                    }
                }
                
                // Add in some random mines. Number of mines = board dimension.
                for (var i = 0; i < dim; i++) {
                    var r1 = Math.floor(Math.random() * dim);
                    var r2 = Math.floor(Math.random() * dim);
                    board[r1][r2].bomb = true;
                    
                    // Increment the numbers of the cells surrounding the mines.
                    try { board[r1-1][r2-1].number++; } catch (e) {}
                    try { board[r1][r2-1].number++; } catch (e) {}
                    try { board[r1][r2+1].number++; } catch (e) {}
                    try { board[r1+1][r2-1].number++; } catch (e) {}
                    try { board[r1+1][r2].number++; } catch (e) {}
                    try { board[r1-1][r2].number++; } catch (e) {}
                    if (cell_shape == "Square") {
                        try { board[r1-1][r2+1].number++; } catch (e) {}
                        try { board[r1+1][r2+1].number++; } catch (e) {}
                    }
                }
            } //newGame()   
            
            // Determines if cell was left-clicked or right-clicked, then calls toggleCell().
            // If this is the first cell clicked in the game, start the timer.
            function detectClick(event, cell) {
                if (!stopwatch_running) {
                    interval = setInterval(timer, 1000);
                    stopwatch_running = true;
                }
                var click_type = event.button;
                toggleCell(cell, click_type);
            }
            
            // timer() updates the clock HTML element, and is called every second by the setInterval() function above.
            // Adds in a leading '0' if the digits are less than 10.
            var total_seconds = 0;
            function timer() {
                total_seconds++;
                var time = document.getElementById("timer-h3");
                var minutes = Math.floor(total_seconds/60);
                minutes = minutes < 10 ? "0" + minutes : minutes;
                var secs = total_seconds % 60;
                secs = secs < 10 ? "0" + secs : secs;
                time.innerHTML = minutes + ":" + secs;
            }
            
            // Stops the timer() function from being called every second. Called when the game is lost/won/reset.
            function stopTimer() {
                clearInterval(interval);
                total_seconds = 0;
                stopwatch_running = false;
            }
            
            // toggleCell() determines what should happen when a cell is clicked (with either a left or right click).
            var total_uncovered_cells = 0;
            function toggleCell(cell, click_type) {
                
                // substring(3) removes 'row' and 'col' from the element IDs.
                var col = parseInt(cell.id.substring(3));
                var row = parseInt(cell.parentElement.parentElement.id.substring(3));                
                var thisCell = board[row][col];
                
                // If the cell has already been uncovered, return from function.
                if (!thisCell.covered) return false;
                
                // For flagging/unflagging cells (with right clicks).
                if (click_type == 2) {
                    if (thisCell.flagged) {
                        thisCell.flagged = false;
                        cell.innerHTML = "";
                    }
                    else {
                        thisCell.flagged = true;
                        cell.innerHTML = '<div class="inner-cell"><img src="Images/flag.png" alt="flag" id="flag-img"></div>';
                        // If number of bombs = number of uncovered cells, the game is won.
                        if (total_uncovered_cells == (dim*dim)-dim) 
                            winGame();
                    }
                    return true;
                }
                
                // For uncovering cells (with left clicks).
                if (click_type == 0) {
                    thisCell.covered = false;
                    cell.setAttribute("style", "background-color: #ff3100");
                    cell.innerHTML = "";
                    total_uncovered_cells++;
                    if (total_uncovered_cells == (dim*dim)-dim)
                        winGame();
                }
                if (thisCell.bomb) {
                    cell.innerHTML = '<div class="inner-cell"><img src="Images/mine.png" alt="mine" id="mine-img"></div>';
                    loseGame();
                    return true;
                }
                if (thisCell.number != 0) {
                    cell.innerHTML = '<div class="inner-cell">' + thisCell.number + '</div>';
                }
                
            }
            
            
            // loseGame() is called when the user uncovers a mine, and therefore, loses the game.
            // Disables the toggling of cells, stops the timer, and displays a 'losing' message.
            function loseGame() {
                disableCells();
                stopTimer();
                var message = document.getElementById("message-div");
                message.innerHTML = '<h3 id="message-h3">You lose!</h3>';
            }
            
            // winGame() is called when the user wins the game (ie. all cells except mines are uncovered, 
            // and all mines are flagged).
            function winGame() {
                stopTimer();
                disableCells();
                var message = document.getElementById("message-div");
                message.innerHTML = '<h3 id="message-h3">You win!</h3>';   
            }
            
            // Disables the toggling of cells by making the "mousedown" attribute do nothing.
            function disableCells() {
                var tbl = document.getElementById("game-table-body").rows;
                    for (var i = 0; i < dim; i++) {
                        row = tbl[i].cells;
                        for (var j = 0; j < dim; j++) {
                            row[j].firstChild.setAttribute("onmousedown", "return false");
                        }
                    }
             }
            
            // resetGame() is called when the user clicks the 'reset' button above the game grid.
            function resetGame() {
                var tbl = document.getElementById("game-table-body");
                while (tbl.firstChild) {
                    tbl.removeChild(tbl.firstChild);
                }
                var message = document.getElementById("message-div");
                message.innerHTML = "";
                total_uncovered_cells = 0;
                stopTimer();
                newGame();
            }
            
        </script>
        
    </head>
    
    <body onload="newGame()">
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
                <div id="game-reset-btn" onclick="resetGame()">
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
            <p>Jack Kearsley (s5007230), July 2017, for 2805ICT.</p>
        </div>
                    
    </body>

</html>