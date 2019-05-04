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
        <link rel="stylesheet" type="text/css" href="css/desktop.css">
        <link rel="shortcut icon" href="images/favicon.png" type="image/png">
        <!-- images made by 'Freepik' from www.flaticon.com -->
        
        <script type="text/javascript">
            var dim = parseInt("<?php echo $dimensions; ?>");
            var cell_shape = "<?php echo $cell_shape; ?>";
            var colour_mode = "<?php echo $colour_mode; ?>" === "True" ? true : false;
            
            // Create instances of the objects, and call setup functions.
            var game_board = new Board();
            game_board.resetBoard();
            var display = new Display();
            var controls = new Controller(game_board, display);
            display.paintTable();
            
            // Board class (model).
            function Board() {
                this.cell = function() {
                    this.covered = true;
                    this.flagged = false;
                    this.number = 0;
                    this.bomb = false;
                    this.color =  "";
                }
                // Set up multidimensional array of cells.
                this.board = new Array(dim);
                this.resetBoard = function() {
                    for (var i = 0; i < dim; i++) {
                        this.board[i] = new Array(dim);
                        for (var j = 0; j < dim; j++) {
                            this.board[i][j] = new this.cell();
                        }
                    }
                    // Add in some random mines. Number of mines = board dimension.
                    for (var i = 0; i < dim; i++) {
                        var r1 = Math.floor(Math.random() * dim);
                        var r2 = Math.floor(Math.random() * dim);
                        this.toggleMine(r1, r2);
                                            
                        if (cell_shape == "Square") {
                            // Increment the numbers of the cells surrounding the mines.
                            try { this.incrementCell(r1-1, r2-1); } catch (e) {}
                            try { this.incrementCell(r1, r2-1); } catch (e) {}
                            try { this.incrementCell(r1, r2+1); } catch (e) {}
                            try { this.incrementCell(r1+1, r2-1); } catch (e) {}
                            try { this.incrementCell(r1+1, r2); } catch (e) {}
                            try { this.incrementCell(r1-1, r2); } catch (e) {}
                            try { this.incrementCell(r1-1, r2+1); } catch (e) {}
                            try { this.incrementCell(r1+1, r2+1); } catch (e) {}
                        }
                        
                        else if (cell_shape == "Hexagon") {
                            // If even row.
                            if (r1 % 2 == 0) {
                                try { this.incrementCell(r1, r2-1); } catch (e) {}
                                try { this.incrementCell(r1, r2+1); } catch (e) {}
                                try { this.incrementCell(r1-1, r2-1); } catch (e) {}
                                try { this.incrementCell(r1-1, r2); } catch (e) {}
                                try { this.incrementCell(r1+1, r2-1); } catch (e) {}
                                try { this.incrementCell(r1+1, r2); } catch (e) {} 
                            }
                            // If odd row.
                            else {
                                try { this.incrementCell(r1, r2-1); } catch (e) {}
                                try { this.incrementCell(r1, r2+1); } catch (e) {}
                                try { this.incrementCell(r1-1, r2); } catch (e) {}
                                try { this.incrementCell(r1+1, r2); } catch (e) {} 
                                try { this.incrementCell(r1+1, r2+1); } catch (e) {} 
                                try { this.incrementCell(r1-1, r2+1); } catch (e) {} 
                            } 
                        }                        
                    }
                }
                // Simple getter/setter methods.
                this.toggleMine = function(row, col) { this.board[row][col].bomb = !this.board[row][col].bomb; }
                this.isMine = function(row, col) { return this.board[row][col].bomb; }
                this.incrementCell = function(row, col) { this.board[row][col].number++; }
                this.getNum = function(row, col) { return this.board[row][col].number; }
                this.isCovered = function(row, col) { return this.board[row][col].covered; }
                this.isFlagged = function(row, col) { return this.board[row][col].flagged; }
                this.flag = function(row, col) { this.board[row][col].flagged = true; }
                this.unflag = function(row, col) { this.board[row][col].flagged = false; }
                this.uncover = function(row, col) { this.board[row][col].covered = false; }
                this.getColor = function(row, col) { return this.board[row][col].color; }
                this.setColor = function(row, col, color) { this.board[row][col].color = color; }
            }
            
            // Display class is responsible for all of the visual displays.
            function Display() {
                var total_seconds = 0;
                this.stopwatch_running = false;
                this.color_palette = ["red", "skyblue", "orange", "green", "purple", "magenta", "gold", "teal", "aqua", "brown", "linen", "lightgray"];
                this.timer = function() {
                    total_seconds++;
                    var time = document.getElementById("timer-h3");
                    var minutes = Math.floor(total_seconds/60);
                    minutes = minutes < 10 ? "0" + minutes : minutes;
                    var secs = total_seconds % 60;
                    secs = secs < 10 ? "0" + secs : secs;
                    time.innerHTML = minutes + ":" + secs;
                }
                // Prints the winning/losing message to the user.
                this.printMessage = function(msg) { 
                    var message = document.getElementById("message-div");
                    if (msg == "win") message.innerHTML = '<h3 id="message-h3">You win!</h3>';
                    else if (msg == "lose") message.innerHTML = '<h3 id="message-h3">You lose!</h3>';
                    else message.innerHTML = "";
                }
                // Stops the timer() function from being called every second. Called when the game is lost/won/reset.
                this.stopTimer = function(interval) {
                    clearInterval(interval);
                    total_seconds = 0;
                    this.stopwatch_running = false;
                }
                // Returns if stopwatch is running.
                this.swrunning = function() { return this.stopwatch_running; }
                
                // Paints a given cell a certain way.
                this.paintCell = function (cell, type) {
                    if (type == "unflag") cell.innerHTML = "";
                    else if (type == "flag") cell.innerHTML = '<div class="inner-cell"><img src="images/flag.png" alt="flag" id="flag-img"></div>';
                    else if (type == "uncover") {
                        if (!colour_mode) {
                            cell.setAttribute("style", "background-color: #ff3100");
                            cell.innerHTML = "";
                        }
                        else {
                            cell.setAttribute("style", "background-color: " + this.getValidColour(cell, "free"));
                            cell.innerHTML = "";
                        }
                    }
                    else if (type == "mine") {
                        cell.innerHTML = '<div class="inner-cell"><img src="images/mine.png" alt="mine" id="mine-img"></div>';
                        if (colour_mode) cell.setAttribute("style", "background-color: " + this.getValidColour(cell, "used"));
                    }
                    // A cell's number is passed after the "num" string, eg. "num3", so use substr() to separate these out.
                    else if (type.substr(0, 3) == "num") cell.innerHTML = '<div class="inner-cell">' + type.substr(3, 1) + '</div>';                    
                
                }//paintCell()
                
                
                // Retrieves a colour for a cell, if type is "free", then it retrieves a colour that hasn't been used yet in the surrounding cells (for painting non-mine cells).
                // if type is "used" then it retrieves a colour that has already been used in the adjacent cells (for the painting of mine cells).
                this.getValidColour = function(cell, type) {
                    var col = parseInt(cell.id.substring(3));
                    var row = parseInt(cell.parentElement.parentElement.id.substring(3));    
                    var used_colors = []
                    try { used_colors.push(game_board.getColor(row, col+1)); } catch(e) {}
                    try { used_colors.push(game_board.getColor(row, col-1)); } catch(e) {}
                    try { used_colors.push(game_board.getColor(row+1, col-1)); } catch(e) {}
                    try { used_colors.push(game_board.getColor(row+1, col)); } catch(e) {}
                    try { used_colors.push(game_board.getColor(row+1, col+1)); } catch(e) {}
                    try { used_colors.push(game_board.getColor(row-1, col-1)); } catch(e) {}
                    try { used_colors.push(game_board.getColor(row-1, col)); } catch(e) {}
                    try { used_colors.push(game_board.getColor(row-1, col+1)); } catch(e) {}

                    // Shuffle the color_palette array.
                    var j, x, i;
                    for (i = this.color_palette.length; i; i--) {
                        j = Math.floor(Math.random() * i);
                        x = this.color_palette[i-1];
                        this.color_palette[i-1] = this.color_palette[j];
                        this.color_palette[j] = x;
                    }
       
                    for (var i = 0; i < this.color_palette.length; i++) {
                        // If you don't find a colour from color_palette in 'used_colors'.
                        if (used_colors.indexOf(this.color_palette[i]) == -1) {
                            if (type == "free") {
                                game_board.setColor(row, col, this.color_palette[i]);
                                return this.color_palette[i];
                            }
                        }
                        else {
                            if (type == "used") {
                                console.log(this.color_palette);
                                console.log("choosing " + this.color_palette[i]);
                                return this.color_palette[i];                                
                            }
                        }
                    }
                    // If no colours used, just return red as a fail-safe default.
                    return "red";
                }//getValidColor()
                
                
                // Generate the initial HTML elements for the table. Sets each cell to call detectClick() when it is clicked. 
                // Gives each cell its unique ID depedning on its location, and applies some classes and styling for each cell-shape type.
                this.paintTable = function() {
                    var tbl = document.getElementById("game-table-body");
                    tbl.parentElement.setAttribute("style", "border: none;");

                    // Insert HTML cells into the table.
                    for (var i = 0; i < dim; i++) {
                        var row = tbl.insertRow(i);
                        row.setAttribute("id", "row" + i );
                        for (var j = 0; j < dim; j++) {
                            var cell = row.insertCell(j);
                            var innerDiv = document.createElement("div");
                            innerDiv.setAttribute("id", "col" + j);
                            innerDiv.setAttribute("onmousedown", "controls.detectClick(event, this)");
                            innerDiv.innerHTML = "&nbsp";
                            if (cell_shape == "Square") {
                                innerDiv.setAttribute("class", "square-cell");
                                cell.appendChild(innerDiv);
                            }
                            else if (cell_shape == "Hexagon") {
                                innerDiv.setAttribute("class", "hexagon-cell-inner");
                                cell.appendChild(innerDiv);
                                cell.setAttribute("class", "hex-outer");
                            }
                        }
                        // If hexagon cells, apply special formatting to every 2nd row.
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

                }//paintTable()  
                
            }//Display() object.
            
            
            
            // Controller object detects user events and responds.
            function Controller(game_board, display) {
                // Determines if cell was left-clicked or right-clicked, then calls toggleCell().
                // If this is the first cell clicked in the game, start the timer.
                this.interval;
                
                // This function is called when a cell is clicked. Toggles the stopwatch if its not already running, and then calls toggleCell().
                this.detectClick = function(event, cell) {
                    if (!display.swrunning()) {
                        this.interval = setInterval(display.timer, 1000);
                        display.stopwatch_running = true;
                    }
                    var click_type = event.button;
                    this.toggleCell(cell, click_type);
                }
                
                // Performs the appropriate actions when a cell is clicked, depending on the status of the cell, and the type of click.
                this.toggleCell = function(cell, click_type) {
                    // substring(3) removes 'row' and 'col' from the element IDs.
                    var col = parseInt(cell.id.substring(3));
                    var row = parseInt(cell.parentElement.parentElement.id.substring(3));                
                    // If the cell has already been uncovered, return from function.
                    if (!game_board.isCovered(row, col)) return false;

                    // For flagging/unflagging cells (with right clicks).
                    if (click_type == 2) {
                        // If the cell is already flagged, unflag it.
                        if (game_board.isFlagged(row, col)) {
                            game_board.unflag(row, col);
                            display.paintCell(cell, "unflag");
                        }
                        // Otherwise, if the cell isn't flagged, flag it now.
                        else {
                            game_board.flag(row, col);
                            display.paintCell(cell, "flag");
                            // If the number of bombs = number of uncovered cells, the game is won.
                            if (game_board.total_uncovered_cells == (dim*dim)-dim) 
                                this.endGame("win");
                        }
                        return true;
                    }
                    // For uncovering cells (with left clicks).
                    if (click_type == 0) {
                        game_board.uncover(row, col);
                        display.paintCell(cell, "uncover");
                        game_board.total_uncovered_cells++;
                        if (game_board.total_uncovered_cells == (dim*dim)-dim) this.endGame("win");
                    }
                    if (game_board.isMine(row, col)) {
                        display.paintCell(cell, "mine");
                        this.endGame("lose");
                        return true;
                    }
                    if (game_board.getNum(row, col) != 0) {
                        display.paintCell(cell, "num" + game_board.getNum(row, col));
                    }
                    else {
                        //this.recursiveUncover(cell, row, col);
                    }
                }//toggleCell()
                
                // Win or lose the game depending on the value of msg.
                this.endGame = function(msg) {
                    display.stopTimer(this.interval);
                    this.disableCells();
                    display.printMessage(msg);   
                }
                
                // Disables all of the cells from being clicked by the user.
                this.disableCells = function() {
                    var tbl = document.getElementById("game-table-body").rows;
                        for (var i = 0; i < dim; i++) {
                            row = tbl[i].cells;
                            for (var j = 0; j < dim; j++) {
                                row[j].firstChild.setAttribute("onmousedown", "return false");
                            }
                        }
                }
                    
                // resetGame() is called when the user clicks the 'reset' button above the game grid.
                this.resetGame = function() {
                    var tbl = document.getElementById("game-table-body");
                    while (tbl.firstChild) {
                        tbl.removeChild(tbl.firstChild);
                    }
                    display.printMessage("");
                    game_board.total_uncovered_cells = 0;
                    display.stopTimer(this.interval);
                    time_box = document.getElementById("timer-h3");
                    time_box.innerHTML = "00:00";
                    game_board.resetBoard();
                    display.paintTable();
                }
                
            }//Controller() object
                
            
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