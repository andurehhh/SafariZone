<?php

    $Pokemons = [
        [
          'name' => 'Pikachu',
          'escapeRate' => 20,
          'catchRate' => 15,
          'pic' => '<a href="https://pokemondb.net/pokedex/pikachu"><img src="https://img.pokemondb.net/sprites/ruby-sapphire/normal/pikachu.png" alt="Pikachu"></a>'
        ],
        [
          'name' => 'togepi',
          'escapeRate' => 10,
          'catchRate' => 45,
          'pic' => '<a href="https://pokemondb.net/pokedex/togepi"><img src="https://img.pokemondb.net/sprites/ruby-sapphire/normal/togepi.png" alt="Togepi"></a>'
        ],
        [
          'name' => 'Chansey',
          'escapeRate' => 20,
          'catchRate' => 10,
          'pic' => '<a href="https://pokemondb.net/pokedex/chansey"><img src="https://img.pokemondb.net/sprites/ruby-sapphire/normal/chansey.png" alt="Chansey"></a>'

        ],
        [
          'name' => 'Snorlax',
          'escapeRate' => 5,
          'catchRate' => 5,
          'pic' => '<a href="https://pokemondb.net/pokedex/snorlax"><img src="https://img.pokemondb.net/sprites/ruby-sapphire/normal/snorlax.png" alt="Snorlax"></a>'

        ]
    ];

    session_start();
    if (!isset($_SESSION['log'])) {
        $_SESSION['log'] = [];
    }
    $maxLogEntries = 2;

    if (!isset($_SESSION['catchRate'])) {
        $randomMon = rand(0,count($Pokemons)-1);
        $_SESSION['currentPic'] = $Pokemons[$randomMon]['pic'];
        $_SESSION['baseCatchRate'] = $Pokemons[$randomMon]['catchRate'];
        $_SESSION['baseEscapeRate'] = $Pokemons[$randomMon]['escapeRate'];
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
          $_SESSION['currentPic'] = '<img src="https://img.pokemondb.net/sprites/items/poke-ball.png" alt="Pokeball">';
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
        global $Pokemons;

        $randomMon = rand(0,count($Pokemons));
        $_SESSION['currentPic'] = $Pokemons[$randomMon]['pic'];
        $_SESSION['baseCatchRate'] = $Pokemons[$randomMon]['catchRate'];
        $_SESSION['baseEscapeRate'] = $Pokemons[$randomMon]['escapeRate'];
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
  <body>
    <center>
      <div
        style="
          box-shadow: 0px 10px 30px grey;
          min-height: none;
          min-width: none;
          height: 500px;
          width: 400px;
          background-color: greenyellow;
          padding: 20px;
          box-sizing: border-box;
        "
      >
        <?php if (!$_SESSION['gameOver']): ?>
        <form method="POST">
          <button type="submit" name="bait" value="bait">Throw Bait</button>
          <button type="submit" name="catch" value="catch">Throw Pokeball</button>
          <button type="submit" name="rock" value="rock">Throw Rock</button>
        </form>
        <?php else: ?>
        <div style="margin-bottom:10px; font-weight:bold;">Finished! Use reset to play again.</div>
        <form method="POST">
          <button type="submit" name="reset" value="reset">RESET</button>
        </form>
        <?php endif; ?>

        <div style="position: relative; width: 70%; margin: 0 auto;">
          <img src="assets/battle-back.jpg" alt="battleground" style="max-width: 70%; border-radius: 10px; display:block;">
            <div style="position: absolute; bottom: 35%; left: 50%; transform: translateX(-50%);">
            <?php echo $_SESSION['currentPic']; ?>
            </div>
        </div>
        <div style="margin:10px 0; color:#000;">
          Catch rate: <?php echo htmlspecialchars($_SESSION['catchRate']); ?><br>
          Escape rate: <?php echo htmlspecialchars($_SESSION['escapeRate']); ?><br>
          State: <?php echo htmlspecialchars($_SESSION['state']); ?><br>
          Turns: <?php echo htmlspecialchars($_SESSION['stateDuration']); ?>


        </div>

        <div class="logs" style="overflow:auto; max-height:150px; width:100%; padding:10px; background:#fff; color:#000; box-sizing:border-box;">
          <?php foreach ($_SESSION['log'] as $entry): ?>
            <p style="margin:4px 0; word-break:break-word;"><?php echo htmlspecialchars($entry); ?></p>
          <?php endforeach; ?>

      </div>
    </center>
  </body>
</html>
