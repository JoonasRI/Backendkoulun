<?php

  // Suoritetaan projektin alustusskripti.
  require_once '../src/init.php';

  // Siistitään polku urlin alusta ja mahdolliset parametrit urlin lopusta.
  // Siistimisen jälkeen osoite /~koodaaja/lanify/tapahtuma?id=1 on 
  // lyhentynyt muotoon /tapahtuma.
  $request = str_replace($config['urls']['baseUrl'],'',$_SERVER['REQUEST_URI']);
  $request = strtok($request, '?');

  // Luodaan uusi Plates-olio ja kytketään se sovelluksen sivupohjiin.
  $templates = new League\Plates\Engine(TEMPLATE_DIR); 
  
  // Selvitetään mitä sivua on kutsuttu ja suoritetaan sivua vastaava
  // käsittelijä.
  switch ($request) {
    case '/':
    case '/ajanvaraus':
      require_once MODEL_DIR . 'model.php';
      $ajanvaraus = haeAjanvaraus();
      echo $templates->render('ajanvaraus',['ajanvaraus' => $ajanvaraus]);
      break;
    
    case '/varaa':
      require_once MODEL_DIR . 'model.php';
      $varaa = haeVaraus($_GET['id']);
      if ($varaa) {
        echo $templates->render('varaa',['varaa' => $varaa]);
      } else {
        echo $templates->render('varausnotfound');
      }
      break;
  
    case '/lisaa_tili':
      echo $templates->render('lisaa_tili');
      break;
  
    default:
      echo $templates->render('notfound');
      break;
  }
  

?> 
