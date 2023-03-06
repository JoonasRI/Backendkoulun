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
        if (isset($_POST['laheta'])) {
          require_once MODEL_DIR . 'henkilo.php';
          $salasana = password_hash($_POST['salasana1'], PASSWORD_DEFAULT);
          $id = lisaaHenkilo($_POST['nimi'],$_POST['sukunimi'],$_POST['email'],$salasana);
          echo "Tili on luotu tunnisteella $id";
          break;
        } else {
          echo $templates->render('lisaa_tili');
          break;
        }
      default:
        echo $templates->render('notfound');
    }
  

?> 
