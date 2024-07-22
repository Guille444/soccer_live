<?php
// Se incluye la clase del modelo.
require_once('../../models/data/pedidos_data.php');

// Se comprueba si existe una acción a realizar, de lo contrario se finaliza el script con un mensaje de error.
if (isset($_GET['action'])) {
    // Se crea una sesión o se reanuda la actual para poder utilizar variables de sesión en el script.
    session_start();
    // Se instancia la clase correspondiente.
    $pedidos = new PedidoData;
    // Se declara e inicializa un arreglo para guardar el resultado que retorna la API.
    $result = array('status' => 0, 'session' => 0, 'message' => null, 'dataset' => null, 'error' => null, 'exception' => null, 'username' => null);
    // Se verifica si existe una sesión iniciada como valoracion$valoracion, de lo contrario se finaliza el script con un mensaje de error.
    if (isset($_SESSION['idEmpleado'])) {
        $result['session'] = 1;
        // Se compara la acción a realizar cuando un valoracion$valoracion ha iniciado sesión.
        switch ($_GET['action']) {
            case 'searchRows':
                if (!Validator::validateSearch($_POST['search'])) {
                    $result['error'] = Validator::getSearchError();
                } elseif ($result['dataset'] = $pedidos->searchRows()) {
                    $result['status'] = 1;
                    $result['message'] = 'Existen ' . count($result['dataset']) . ' coincidencias';
                } else {
                    $result['error'] = 'No hay coincidencias';
                }
                break;
            case 'readAll':
                if ($result['dataset'] = $pedidos->readAll()) {
                    $result['status'] = 1;
                    $result['message'] = 'Existen ' . count($result['dataset']) . ' registros';
                } else {
                    $result['error'] = 'No existen pedidos registrados';
                }
                break;
            case 'readOne':
                if (!$pedidos->setId($_POST['idPedido'])) {
                    $result['error'] = 'Pedido incorrecto';
                } elseif ($result['dataset'] = $pedidos->readOne()) {
                    $result['status'] = 1;
                } else {
                    $result['error'] = 'Pedido inexistente';
                }
                break;
            case 'updateRow':
                $_POST = Validator::validateForm($_POST);
                if (
                    !$pedidos->setId($_POST['idPedido']) or
                    !$pedidos->setEstado($_POST['estadoPedido'])
                ) {
                    $result['error'] = $pedidos->getDataError();
                } elseif ($pedidos->updateRow()) {
                    $result['status'] = 1;
                    $result['message'] = 'Pedido modificado correctamente';
                } else {
                    $result['error'] = 'Ocurrió un problema al modificar el pedido';
                }

                break;
                case 'CantidadEstadoPedidos':
                    if ($result['dataset'] = $pedidos->CantidadEstadoPedidos()) {
                        $result['status'] = 1;
                    } else {
                        $result['error'] = 'No hay datos disponibles';
                    }
                    break;
                    case 'PorsentajeEstadoPedidos':
                        if ($result['dataset'] = $pedidos->PorsentajeEstadoPedidos()) {
                            $result['status'] = 1;
                        } else {
                            $result['error'] = 'No hay datos disponibles';
                        }
                        break;
                        case 'prediccionGanancia':
                            if (
                                !$pedidos->setId($_POST['limit']) 
                            ) {
                                $result['error'] = $pedidos->getDataError();
                            } elseif ($result['dataset'] = $pedidos->prediccionGanancia()) {
                                $result['status'] = 1;
                            } else {
                                $result['error'] = 'No hay datos disponibles';
                            }
                            break;
        }
    } else {
        // Se compara la acción a realizar cuando el valoracion$valoracion no ha iniciado sesión.
        switch ($_GET['action']) {
            case 'readUsers':
                if ($pedidos->readAll()) {
                    $result['status'] = 1;
                    $result['message'] = 'Debe autenticarse para ingresar';
                } else {
                    $result['error'] = 'Debe crear un valoracion$valoracion para comenzar';
                }
                break;
            default:
                $result['error'] = 'Acción no disponible fuera de la sesión';
        }
    }
    // Se obtiene la excepción del servidor de base de datos por si ocurrió un problema.
    $result['exception'] = Database::getException();
    // Se indica el tipo de contenido a mostrar y su respectivo conjunto de caracteres.
    header('Content-type: application/json; charset=utf-8');
    // Se imprime el resultado en formato JSON y se retorna al controlador.
    print(json_encode($result));
} else {
    print(json_encode('Recurso no disponible'));
}