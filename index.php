<?php

include_once('config.php');

echo <<<END
<!DOCTYPE html>
<html lang="en">
	<head>
		<link rel="icon" type="image/png" href="icon.png">
		<link rel="stylesheet" href="style.css">
		<title>
END;

if(!$loggedin){
	echo "Snake | Login";
} else {
	echo "Snake | Play";
}

echo <<<END
		</title>
	</head>
	<body>
END;

if(!$loggedin){
	echo <<<END
		<div id='centred1'>
			<div id='header'>
				<img float='left' src='icon.png' alt=''></img>
				Snake
			</div>
			<form id='login' name='login' method='post'>
				<h4>Login<h4>
				<input type='text' name='username' placeholder='Username'></input><br/>
				<input type='password' name='password' placeholder='Password'></input><br/>
				<button name='submit'>Login</button>
END;

		$error = "";
		$username = "";
		$password = "";
	
		if(isset($_POST['username']) && isset($_POST['password'])){
			$username = check_string($_POST['username']);
			$password = check_string($_POST['password']);


			if($username == "" || $password == ""){
				$error = "All fields must be filled";
			} else {
				$result = mysqli_query($connection, "SELECT * FROM users WHERE username='$username'");

				if(!mysqli_num_rows($result)){
					$error = "Wrong username or password";
				} else {
					$row = mysqli_fetch_row($result);

					if(password_verify($password, $row[2])){
						$_SESSION['id'] = $row[0];
						$_SESSION['username'] = $username;
						$_SESSION['password'] = $password;
						
						$loggedin = TRUE;
		
						header("Location: ./");
					} else {
						$error = "Wrong username or password";
					}
				}
			}
		}
		
		if($error != ""){
			echo "<div id='error'>$error</div>";
		}

	echo <<<END
			</form>
			<h4>Singup<h4>
			<form id='singup' name='singup' method='post'>
				<input type='text' name='username2' placeholder='Username'></input><br/>
				<input type='password' name='password2' placeholder='Password'></input><br/>
				<button name='submit2'>Singup</button>
END;

		$error = "";
		$username = "";
		$password = "";
	
		if(isset($_POST['username2']) && isset($_POST['password2'])){
			$username = check_string($_POST['username2']);
			$password = check_string($_POST['password2']);

			if($username == "" || $password == ""){
				$error = "All fields must be filled";
			} else {
				$result = mysqli_query($connection, "SELECT * FROM users WHERE username='$username'");

				if(mysqli_num_rows($result)){
					$error = "This username is already taken";
				} else {
					$password_hash = password_hash($password, PASSWORD_BCRYPT);
					$result = mysqli_fetch_row(mysqli_query($connection, "SELECT MAX(id) AS id FROM users"));					
					$id = $result[0];
					$id += 1;
	
					mysqli_query($connection, "INSERT INTO users VALUES('$id', '$username', '$password_hash', '0')");
	
					$_SESSION['username'] = $username;
					$_SESSION['password'] = $password;
					$_SESSION['id'] = $id;
	
					$loggedin = TRUE;
	
					header("Location: ./");
				}
			}
		}
		
		if($error != ""){
			echo "<div id='error'>$error</div>";
		}

echo <<<END
		</div>
END;
} else {
	if(isset($_GET['logout'])){
		session_destroy();
		header("Location: ./");
	}

	echo <<<END
		<div id='centred2'>
			<div id='logout'>
				<a href='?logout'>Logout</a>
				<form method='post'>
					<input id='hardness' onchange='setHardness()' type='number' name='hardness' min='1' max='10' value='5' style='float: right; margin-top: -40px;'></input>
				</form>
			</div>

			<canvas id='canvas'></canvas>
			<script>
var

END;
	$result = mysqli_fetch_row(mysqli_query($connection, "SELECT * FROM users WHERE username='" . $_SESSION['username'] . "'"));


	echo "MAX_SCORE = " . $result[3] .",";
	echo <<<END

COLS = 26,
ROWS = 26,
EMPTY = 0,
SNAKE = 1,
FRUIT = 2,
WALL = 3,
LEFT  = 0,
UP    = 1,
RIGHT = 2,
DOWN  = 3,
KEY_LEFT  = 37,
KEY_UP    = 38,
KEY_RIGHT = 39,
KEY_DOWN  = 40,

HARDNESS = 5,
GAMEOVER = false,

canvas,	
ctx,	 
keystate, 
frames,   
score;

grid = {
	width: null,
	height: null,
	_grid: null,

	init: function(d, c, r) {
		this.width = c;
		this.height = r;
		this._grid = [
			[3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3],
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3],  
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3],  
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3],  
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3, 0, 0, 0, 0, 3],  
			[3, 0, 0, 0, 0, 0, 3, 3, 3, 3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3, 0, 0, 0, 0, 3],  
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3, 0, 0, 0, 0, 3],
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3],
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3],
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3, 0, 0, 0, 0, 3],
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3, 0, 0, 0, 0, 3],
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3, 3, 0, 3, 3, 0, 0, 0, 0, 3, 0, 0, 0, 0, 3],
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3, 0, 0, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3],
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3],
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3, 0, 0, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3],
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3, 3, 0, 3, 3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3],
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3],
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3],
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3],
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3],
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3],
			[3, 0, 0, 3, 3, 3, 0, 3, 3, 3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3, 0, 0, 0, 3],
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3, 0, 0, 0, 3],
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3],
			[3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3],
			[3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3],
		];
	},

	set: function(val, x, y) {
		this._grid[x][y] = val;
	},

	get: function(x, y) {
		return this._grid[x][y];
	}
}

snake = {
	direction: null,
	last: null,
	_queue: null,

	init: function(d, x, y) {
		this.direction = d;
		this._queue = [];
		this.insert(x, y);
		this.insert(x + 1, y);
		this.insert(x + 2, y);	
	},

	insert: function(x, y) {
		this._queue.unshift({x:x, y:y});
		this.last = this._queue[0];
	},

	remove: function() {

		return this._queue.pop();
	}
};

function setFood() {
	var empty = [];

	for (var x=0; x < grid.width; x++) {
		for (var y=0; y < grid.height; y++) {
			if (grid.get(x, y) === EMPTY) {
				empty.push({x:x, y:y});
			}
		}
	}

	var randpos = empty[Math.round(Math.random()*(empty.length - 1))];
	grid.set(FRUIT, randpos.x, randpos.y);
}

function setHardness(){
	HARDNESS = document.getElementById('hardness').value;
}

function main() {
	canvas = document.getElementById('canvas');
	canvas.width = COLS*20;
	canvas.height = ROWS*20;
	ctx = canvas.getContext("2d");

	ctx.font = "12px Helvetica";
	frames = 0;
	keystate = {};
	
	document.addEventListener("keydown", function(evt) {
		keystate[evt.keyCode] = true;
	});
	document.addEventListener("keyup", function(evt) {
		delete keystate[evt.keyCode];
	});

	init();
	loop();
}

function init() {
	score = 0;
	grid.init(EMPTY, COLS, ROWS);
	var sp = {x:10, y:10};
	snake.init(UP, sp.x, sp.y);
	grid.set(SNAKE, sp.x, sp.y);
	setFood();

	GAMEOVER = false;
}

function loop() {
	update();
	draw();

	window.requestAnimationFrame(loop, canvas);
}

var getXmlHttp = function(){
	var xmlhttp;
	
	try{
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e){
		try {
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	    } catch (E){
			xmlhttp = false;
		}
	}

	if(!xmlhttp && typeof XMLHttpRequest != 'undefined'){
		xmlhttp = new XMLHttpRequest();
	}

	return xmlhttp;
}


function update() {
	frames++;

	
	if(GAMEOVER){
		delay(1500);

		GAMEOVER = false;

		init();
	}

	if(!GAMEOVER){
		if (keystate[KEY_LEFT] && snake.direction !== RIGHT) {
			console.log('Snake turned left');
			snake.direction = LEFT;
		}
		if (keystate[KEY_UP] && snake.direction !== DOWN) {
			console.log('Snake turned up');
			snake.direction = UP;
		}
		if (keystate[KEY_RIGHT] && snake.direction !== LEFT) {
			console.log('Snake turned right');
			snake.direction = RIGHT;
		}
		if (keystate[KEY_DOWN] && snake.direction !== UP) {
			console.log('Snake turned down');
			snake.direction = DOWN;
		}

		if (frames%HARDNESS === 0) {
			var nx = snake.last.x;
			var ny = snake.last.y;

			switch (snake.direction) {
				case LEFT:
					nx--;
					break;
				case UP:
					ny--;
					break;
				case RIGHT:
					nx++;
					break;
				case DOWN:
					ny++;
					break;
			}

			if (0 > nx || nx > grid.width-1  ||
				0 > ny || ny > grid.height-1 ||
				grid.get(nx, ny) === SNAKE || grid.get(nx, ny) === WALL
			) {
				console.log('Snake died');

				if(MAX_SCORE <= score){
					MAX_SCORE = score;

					var xhttp = new XMLHttpRequest();

					xhttp.open("GET", "score.php?setscore=" + MAX_SCORE, true);
					xhttp.send();
				}

				return gameover();
			}

			if (grid.get(nx, ny) === FRUIT) {
				score++;
				setFood();

				if(score > MAX_SCORE){
					MAX_SCORE = score;
				}
			} else {
				var tail = snake.remove();
				
				if(grid.get(tail.x, tail.y) != WALL){
					grid.set(EMPTY, tail.x, tail.y);
				}
			}

			grid.set(SNAKE, nx, ny);
			snake.insert(nx, ny);
		}
	}
}

function draw() {
	var tw = canvas.width/grid.width;
	var th = canvas.height/grid.height;

	for (var x=0; x < grid.width; x++) {
		for (var y=0; y < grid.height; y++) {
			if(GAMEOVER){
				ctx.fillStyle = "#fff";
			} else {
				switch (grid.get(x, y)) {
					case EMPTY:
						ctx.fillStyle = "#fff";
						break;
					case SNAKE:
						ctx.fillStyle = "#1abc9c";
						break;
					case FRUIT:
						ctx.fillStyle = "#f00";
						break;
					case WALL:
						ctx.fillStyle = "#000";
						break;
				}
			}
			ctx.fillRect(x*tw, y*th, tw, th);
		}
	}

	ctx.fillStyle = "#000";

	if(GAMEOVER){
		ctx.fillText("GAMEOVER!", canvas.width/2 - 60, canvas.height/2, 120);
	} else {
		ctx.fillText("SCORE: " + score, 30, canvas.height-50);
		ctx.fillText("MAX SCORE: " + MAX_SCORE, 30, canvas.height-30);
	}
}

function delay(millis) {
	var date = new Date();
	var curDate = null;

	do {
		curDate = new Date(); 
	} while(curDate-date < millis);
} 

function gameover(){
	GAMEOVER = true;

	console.log("Game over");

	ctx.fillStyle = "#fff";

	var tw = canvas.width/grid.width;
	var th = canvas.height/grid.height;

	for (var x=0; x < grid.width; x++) {
		for (var y=0; y < grid.height; y++) {
			ctx.fillRect(x*tw, y*th, tw, th);
		}
	}
}

main();	
			</script>
		</div>
END;
}

echo <<<END
	</body>
</html>
END;
?>
