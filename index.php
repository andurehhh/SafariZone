<?php
    session_start();
    if (!isset($_SESSION['log'])) {
        $_SESSION['log'] = [];
    }
    $maxLogEntries = 3;

    if (!isset($_SESSION['catchRate'])) {
        $_SESSION['catchRate'] = 30;
        $_SESSION['escapeRate'] = 30;
        $_SESSION['catchThreshold'] = 10;
        $_SESSION['escapeThreshold'] = 10;
    }

    if (!isset($_SESSION['gameOver'])) {
        $_SESSION['gameOver'] = false;
    }

    if (isset($_POST['reset'])) {
        restartGame();
    }

    //restart game
    function restartGame(){
        $_SESSION['catchRate'] = 30;
        $_SESSION['escapeRate'] = 30;
        $_SESSION['catchThreshold'] = 10;
        $_SESSION['escapeThreshold'] = 15;
        $_SESSION['gameOver'] = false;
        $_SESSION['log'] = [];
    }
    //see if it escapes
    function pokemonEscape(){
      $roll = rand(1,100);
      if($roll <= $_SESSION['escapeRate']){
        $_SESSION['gameOver'] = true;
        array_push($_SESSION['log'],"oh no! the pokemon escaped. Roll:$roll");
        return true;
        }
      else{
        array_push($_SESSION['log'],"the pokemon is waiting patiently... Roll:$roll");
        return false;
        }
    }
    //change escapeRate
    function decreaseEscapeRate(){
      if($_SESSION['escapeRate'] > $_SESSION['escapeThreshold']){
      $_SESSION['escapeRate'] = $_SESSION['escapeRate'] / 1.5;
      }
      else{
        $_SESSION['escapeRate'];
      }
    }
    function decreaseCatchRate(){
      if($_SESSION['catchRate'] > $_SESSION['catchThreshold']){
        $_SESSION['catchRate'] = $_SESSION['catchRate'] / 1.5;
      }
      else{
        $_SESSION['catchRate'];
      }
    }
    function increaseCatchRate(){
      if($_SESSION['catchRate'] < 95){
        $_SESSION['catchRate'] = $_SESSION['catchRate'] * 1.5;
      }
      else{
        $_SESSION['catchRate'];
      }
    }

    //Catching
    if (isset($_POST['catch']) && !$_SESSION['gameOver'])
      {
        $rate = $_SESSION['catchRate'];
        $roll = rand(1,100);
        if($roll <= $_SESSION['catchRate']){
          array_push($_SESSION['log'],"gotcha! the pokemon was caught. Roll:$roll");
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
        decreaseCatchRate();
        decreaseEscapeRate();

        array_push($_SESSION['log'],"you threw a bait! catch rate decreased, escape rate decreased.");

        pokemonEscape();

        if (count($_SESSION['log']) > $maxLogEntries) {
            $_SESSION['log'] = array_slice($_SESSION['log'], -$maxLogEntries);
        }
      }

    //Rocks
    if(isset($_POST['rock']) && !$_SESSION['gameOver'])
      {
        increaseCatchRate();
        array_push($_SESSION['log'],"you threw a rock! catch rate is increased but it is now angry!");

        pokemonEscape();

        if (count($_SESSION['log']) > $maxLogEntries) {
            $_SESSION['log'] = array_slice($_SESSION['log'], -$maxLogEntries);
        }
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
          height: 400px;
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

        <div style="margin:10px 0; color:#000;">
          Catch rate: <?php echo htmlspecialchars($_SESSION['catchRate']); ?><br>
          Escape rate: <?php echo htmlspecialchars($_SESSION['escapeRate']); ?>
        </div>

        <div class="logs" style="overflow:auto; max-height:150px; width:100%; padding:10px; background:#fff; color:#000; box-sizing:border-box;">
          <?php foreach ($_SESSION['log'] as $entry): ?>
            <p style="margin:4px 0; word-break:break-word;"><?php echo htmlspecialchars($entry); ?></p>
          <?php endforeach; ?>

      </div>
    </center>
  </body>
</html>
