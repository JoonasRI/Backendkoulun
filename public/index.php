<?php
  // Aloitetaan istunnot.
  session_start();

  // Suoritetaan projektin alustusskripti.
  require_once '../src/init.php';
  // Haetaan kirjautuneen käyttäjän tiedot.
  if (isset($_SESSION['user'])) {
    require_once MODEL_DIR . 'appointmentuser.php';
    $loggeduser = haeHenkilo($_SESSION['user']);
  } else {
    $loggeduser = NULL;
  }

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
      require_once MODEL_DIR . 'doctors.php';
      $ajanvaraus = haeAjanvaraus();
      echo $templates->render('ajanvaraus',['ajanvaraus' => $ajanvaraus]);
      break;
     case '/varaa':
        require_once MODEL_DIR . 'doctors.php';
        require_once MODEL_DIR . 'ilmoittautuminen.php';
        $varaa = haeVaraus($_GET['id']);
        if ($varaa) {
          if ($loggeduser) {
            $ilmoittautuminen = haeIlmoittautuminen($loggeduser['idhenkilo'],$varaa['doc_id']);
          } else {
            $ilmoittautuminen = NULL;
          }
          echo $templates->render('varaa',['varaa' => $varaa,
                                               'ilmoittautuminen' => $ilmoittautuminen,
                                               'loggeduser' => $loggeduser]);
        } else {
          echo $templates->render('varausnotfound');
        }
        break;
  
        case '/lisaa_tili':
          if (isset($_POST['laheta'])) {
            $formdata = cleanArrayData($_POST);
            require_once CONTROLLER_DIR . 'tili.php';
            $tulos = lisaaTili($formdata,$config['urls']['baseUrl']);    
            if ($tulos['status'] == "200") {
              echo $templates->render('tili_luotu', ['formdata' => $formdata]);
              break;
            }
            echo $templates->render('lisaa_tili', ['formdata' => $formdata, 'error' => $tulos['error']]);
            break;
          } else {
            echo $templates->render('lisaa_tili', ['formdata' => [], 'error' => []]);
            break;
          }
          case "/kirjaudu":
            if (isset($_POST['laheta'])) {
              require_once CONTROLLER_DIR . 'kirjaudu.php';
              if (tarkistaKirjautuminen($_POST['email'],$_POST['salasana'])) {
                require_once MODEL_DIR . 'appointmentuser.php';
                $user = haeHenkilo($_POST['email']);
                if ($user['vahvistettu']) {
                  session_regenerate_id();
                  $_SESSION['user'] = $user['email'];
                  header("Location: " . $config['urls']['baseUrl']);
                } else {
                  echo $templates->render('kirjaudu', [ 'error' => ['virhe' => 'Tili on vahvistamatta! Ole hyvä, ja vahvista tili sähköpostissa olevalla linkillä.']]);
                }
              } else {
                echo $templates->render('kirjaudu', [ 'error' => ['virhe' => 'Väärä käyttäjätunnus tai salasana!']]);
              }
            } else {
              echo $templates->render('kirjaudu', [ 'error' => []]);
            }
            break;
      
            case "/logout":
              require_once CONTROLLER_DIR . 'kirjaudu.php';
              logout();
              header("Location: " . $config['urls']['baseUrl']);
              break;  
              case '/about';
              echo $templates->render('about');
              break;
              case '/etusivu';
              echo $templates->render('etusivu');
              break;
              case '/ilmoittaudu':
                if ($_GET['id']) {
                  require_once MODEL_DIR . 'ilmoittautuminen.php';
                  $varaa = $_GET['id'];
                  if ($loggeduser) {
                    $date = date('Y-m-d H:i:s');
                    lisaaIlmoittautuminen($loggeduser['idhenkilo'], $varaa, $date);
                 }                 
                  header("Location: varaa?id=$varaa");
                } else {
                  header("Location: varaa");
                }
                break;
                
                case '/peru':
                  if ($_GET['id']) {
                    require_once MODEL_DIR . 'ilmoittautuminen.php';
                    $varaa = $_GET['id'];
                    if ($loggeduser) {
                      poistaIlmoittautuminen($loggeduser['idhenkilo'],$varaa);
                    }
                    header("Location: varaa?id=$varaa");
                  } else {
                    header("Location: varaa");  
                  }
                  break;
                  case "/vahvista":
                    if (isset($_GET['key'])) {
                      $key = $_GET['key'];
                      require_once MODEL_DIR . 'appointmentuser.php';
                      if (vahvistaTili($key)) {
                        echo $templates->render('tili_aktivoitu');
                      } else {
                        echo $templates->render('tili_aktivointi_virhe');
                      }
                    } else {
                      header("Location: " . $config['urls']['baseUrl']);
                    }
                    break;
                    case "/tilaa_vaihtoavain":
                      $formdata = cleanArrayData($_POST);
                      // Tarkistetaan, onko lomakkeelta lähetetty tietoa.
                      if (isset($formdata['laheta'])) {    
                  
                        // TODO vaihtoavaimen tilauskäsittely
                  
                      } else {
                        // Lomakeelta ei ole lähetetty tietoa, tulostetaan lomake.
                        echo $templates->render('tilaa_vaihtoavain_lomake');
                      }
                      break;
                
              
          default:
            echo $templates->render('notfound');
        }
    
?> 
