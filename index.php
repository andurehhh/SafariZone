<?php

    require_once 'db.php';

    function getRandomPokemon() {
        global $conn;
        $result = mysqli_query($conn, "SELECT * FROM pokemon ORDER BY RAND() LIMIT 1");

        if($result && mysqli_num_rows($result) > 0){
          $row = mysqli_fetch_assoc($result);

          $nameFormatted = strtolower($row['pokemon_name']);
          $nameFormatted = str_replace(['♀', '♂', "'", ' '], ['-f', '-m', '', '-'], $nameFormatted);
            
          $pic = '<a href="https://pokemondb.net/pokedex/' . $nameFormatted . '"><img src="https://img.pokemondb.net/sprites/diamond-pearl/normal/' . $nameFormatted . '.png" alt="' . $row['pokemon_name'] . '"></a>';
          
          return [
            'name' =>$row['pokemon_name'],
            'catchRate' =>$row['catch_rate'],
            'escapeRate' =>$row['escape_rate'],
            'pic'=> $pic
          ];
        }
        // Fallback if the database is empty or connection fails
        return [
            'name' => 'Pikachu',
            'catchRate' => 15,
            'escapeRate' => 20,
            'pic' => '<a href="https://pokemondb.net/pokedex/pikachu"><img src="https://img.pokemondb.net/sprites/ruby-sapphire/normal/pikachu.png" alt="Pikachu"></a>'
        ];
    }
    

    session_start();
    if (!isset($_SESSION['log'])) {
        $_SESSION['log'] = [];
    }
    $maxLogEntries = 5;

    if (!isset($_SESSION['catchRate'])) {
        $randomMon = getRandomPokemon();
        $_SESSION['currentPic'] = $randomMon['pic'];
        $_SESSION['baseCatchRate'] = $randomMon['catchRate'];
        $_SESSION['baseEscapeRate'] = $randomMon['escapeRate'];
        $_SESSION['catchThreshold'] = 10;
        $_SESSION['escapeThreshold'] = 10;
        $_SESSION['state'] = 'neutral';
        $_SESSION['stateDuration'] = 0;
        $_SESSION['catchRate'] = $_SESSION['baseCatchRate'];
        $_SESSION['escapeRate'] = $_SESSION['baseEscapeRate'];
    }

    if (!isset($_SESSION['gameOver'])) {
        $_SESSION['gameOver'] = false;
    }

    if (isset($_POST['reset'])) {
        restartGame();
    }

    //Catching
    if (isset($_POST['catch']) && !$_SESSION['gameOver'])
      {
        $rate = $_SESSION['catchRate'];
        $roll = rand(1,100);
        if($roll <= $_SESSION['catchRate']){
          array_push($_SESSION['log'],"gotcha! the pokemon was caught. Roll:$roll");
          $_SESSION['currentPic'] = '<img src="https://img.pokemondb.net/sprites/items/safari-ball.png" alt="Pokeball">';
          $_SESSION['gameOver'] = true;
        }
        else{
          array_push($_SESSION['log'],"oh no! the pokemon broke free.");
          pokemonEscape();
        }

        if (count($_SESSION['log']) > $maxLogEntries) {
            $_SESSION['log'] = array_slice($_SESSION['log'], -$maxLogEntries);
        }
      }

    //Bait
    if(isset($_POST['bait']) && !$_SESSION['gameOver'])
      {
        if($_SESSION['state'] == 'neutral' || $_SESSION['state'] == 'angry'){
          $_SESSION['stateDuration'] = 0;
          $_SESSION['stateDuration'] += rand(1,5);
          $_SESSION['state'] = 'eating';
          decreaseCatchRate();
          decreaseEscapeRate();
        }

        else{
          $_SESSION['stateDuration'] = $_SESSION['stateDuration'] + rand(1,5);
        }
        array_push($_SESSION['log'],"you threw a bait! catch rate decreased, escape rate decreased.");

        pokemonEscape();

        if (count($_SESSION['log']) > $maxLogEntries) {
            $_SESSION['log'] = array_slice($_SESSION['log'], -$maxLogEntries);
        }
      }

    //Rocks
    if(isset($_POST['rock']) && !$_SESSION['gameOver'])
      {
        if($_SESSION['state'] == 'neutral' || $_SESSION['state'] == 'eating'){
          $_SESSION['state'] = 'angry';
          $_SESSION['stateDuration'] = 0;
          $_SESSION['stateDuration'] += rand(1,5); 
          increaseCatchRate();
          increaseEscapeRate();
          }
        else{
          $_SESSION['stateDuration'] += rand(1,5); 
          }

        array_push($_SESSION['log'],"you threw a rock! catch rate is increased but it is now angry!");

        pokemonEscape();

        if (count($_SESSION['log']) > $maxLogEntries) {
            $_SESSION['log'] = array_slice($_SESSION['log'], -$maxLogEntries);
        }
      }
    


        //restart game
    function restartGame(){
        $randomMon = getRandomPokemon();
        $_SESSION['currentPic'] = $randomMon['pic'];
        $_SESSION['baseCatchRate'] = $randomMon['catchRate'];
        $_SESSION['baseEscapeRate'] = $randomMon['escapeRate'];
        $_SESSION['catchRate'] = $_SESSION['baseCatchRate'];
        $_SESSION['escapeRate'] = $_SESSION['baseEscapeRate'];
        $_SESSION['catchThreshold'] = 10;
        $_SESSION['escapeThreshold'] = 15;
        $_SESSION['gameOver'] = false;
        $_SESSION['log'] = [];
        $_SESSION['stateDuration'] = 0;
        $_SESSION['state'] = 'neutral';

    }
    //see if it escapes
    function pokemonEscape(){
      $roll = rand(1,100);
      if($roll <= $_SESSION['escapeRate']){
        $_SESSION['gameOver'] = true;
        array_push($_SESSION['log'],"The pokemon fled. Roll:$roll");
        $_SESSION['currentPic'] = '';
        return true;
        }
      else{
        array_push($_SESSION['log'],"the pokemon is waiting patiently... Roll:$roll");
        
          if($_SESSION['state'] == "angry" || $_SESSION['state'] == 'eating'){
            if($_SESSION['stateDuration'] == 1){
              $_SESSION['state'] = "neutral";
              $_SESSION['catchRate'] = $_SESSION['baseCatchRate'];
              $_SESSION['escapeRate'] = $_SESSION['baseEscapeRate'];

            }  
              $_SESSION['stateDuration']--;
            }
        return false;
        }
    }
    //change escapeRate
    function decreaseEscapeRate(){
      if($_SESSION['escapeRate'] > $_SESSION['escapeThreshold']){
      $_SESSION['escapeRate'] = $_SESSION['baseEscapeRate'] / 2;
      }
      else{
        $_SESSION['escapeRate'];
      }
    }
    function decreaseCatchRate(){
      if($_SESSION['catchRate'] > $_SESSION['catchThreshold']){
        $_SESSION['catchRate'] = $_SESSION['baseCatchRate'] / 2;
      }
      else{
        $_SESSION['catchRate'];
      }
    }
    function increaseCatchRate(){
      if($_SESSION['catchRate'] < 95){
        $_SESSION['catchRate'] = $_SESSION['baseCatchRate'] * 2;
      }
      else{
        $_SESSION['catchRate'];
      }
    }
    function increaseEscapeRate(){
      if($_SESSION['escapeRate'] < 95){
        $_SESSION['escapeRate'] = $_SESSION['baseEscapeRate'] * 2;
      }
      else{
        $_SESSION['escapeRate'];
      }
    }


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
    }
    ?>

<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="assets/styles.css">
  </head>
  <body>
      <div class="safari-box">
        <?php if (!$_SESSION['gameOver']): ?>
        <form method="POST" class="gb-button-container">
          <button type="submit" name="bait" value="bait" class="gb-button">Bait</button>
          <button type="submit" name="catch" value="catch" class="gb-button">Poke<br>ball</button>
          <button type="submit" name="rock" value="rock" class="gb-button">Rock</button>
        </form>
        <?php else: ?>
        <div style="margin-bottom:10px; font-weight:bold; color: white; text-shadow: 1px 1px 2px #000;">Finished! Use reset to play again.</div>
        <form method="POST" class="gb-button-container" style="transform: none;">
          <button type="submit" name="reset" value="reset" class="gb-button reset-btn">RESET</button>
        </form>
        <?php endif; ?>

        <div style="position: relative; width: 70%; margin: 0 auto; border: 4px solid #333; border-radius: 10px; background: #000;">
          <img src="assets/battle-back.jpg" alt="battleground" style="width: 100%; border-radius: 5px; display:block;">
            <div class="pokemon-sprite" style="position: absolute; bottom: 25%; left: 50%; transform: translateX(-50%);">
            <?php echo $_SESSION['currentPic']; ?>
            </div>
        </div>
        <div class="stats-text">
          Catch rate: <?php echo htmlspecialchars($_SESSION['catchRate']); ?><br>
          Escape rate: <?php echo htmlspecialchars($_SESSION['escapeRate']); ?><br>
          State: <?php echo htmlspecialchars($_SESSION['state']); ?><br>
          Turns: <?php echo htmlspecialchars($_SESSION['stateDuration']); ?>
        </div>

        <div class="logs" style="overflow:auto; max-height:150px; width:90%; padding:10px; background:#fff; color:#000; box-sizing:border-box; border-radius: 10px; border: 3px solid #333; text-align: left; font-size: 14px;">
          <?php foreach ($_SESSION['log'] as $entry): ?>
            <p style="margin:4px 0; word-break:break-word; border-bottom: 1px dashed #ccc; padding-bottom: 4px;"><?php echo htmlspecialchars($entry); ?></p>
          <?php endforeach; ?>
        </div>
      </div>
  </body>
</html>
