<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use App\Models\CtaCte;
use App\Models\Productos;
use App\Models\Proveedores;
use App\Repositories\CamposEditadosRepository;
use App\Repositories\IndexRepository;
use Illuminate\Http\Request;
use App\Repositories\MovimientosRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CtaCteController extends Controller
{
    private $movimientosRepository;
    private $indexRepository;

    public function __construct(IndexRepository $indexRepository, MovimientosRepository $movimientosRepository)
    {
        $this->movimientosRepository = $movimientosRepository;    
        $this->indexRepository = $indexRepository;    
    }

    public function index() {
        $proveedor = request()->get('proveedor');
        $cliente = request()->get('cliente');

        $cuentas = $this->indexRepository->indexCuentas($proveedor, $cliente);

        return response()->json(['error' => false, 'allCuentas' => CtaCte::all(), 'cuentasFiltro' => $cuentas->get()]);
    }


    public function nuevaCtaCte(Request $request) {
        $req = $request->all();
        $usuario = $req['usuario'];
        $req = $req['data'];
        
        try {
            DB::beginTransaction();

            //PUede ser proveedor o cliente que necesita abrir una cuenta
            if($this->chequearSiExiste($req['proveedor'],$req['tipoCuenta'])){
                return response()->json(['error' => true, 'data' => 'Esa persona ya tiene una cuenta']);
            }

            $cuenta = CtaCte::create([
                'proveedor_id' => $req['tipoCuenta'] === 'p' ? $req['proveedor'] : null,
                'cliente_id' => $req['tipoCuenta'] === 'c' ? $req['proveedor'] : null,
                'saldo' => $req['saldo'],
                'tipo_cuenta' => $req['tipoCuenta'],
            ]);

            $this->movimientosRepository->guardarMovimiento(
                'cuentas_corrientes', 'ALTA', $usuario, $cuenta->id, null, null, null
            );

            DB::commit();

        } catch (\Throwable $e) {
            Log::error($e->getMessage() . $e->getTraceAsString());
            DB::rollBack();
            return response()->json(['error' => true, 'data' => $e->getMessage()]);
        }

        return response()->json(['status' => 200]);
    }

    public function editarCuenta(Request $request, CamposEditadosRepository $camposEditadosRepository) {
        $req = $request->all();
        $usuario = $req['usuario'];
        $req = $req['data'];
        
        try {
            DB::beginTransaction();

            $cuenta = CtaCte::whereId($req['id']);

            $cambios = $this->buscarCamposEditados($cuenta, $req);

            $cuenta->update([
                "saldo" => $req['saldo'],
                ($req['esCliente'] ? "cliente_id" : "proveedor_id") => $req['proveedor']
            ]);
            if ($cambios) { //EDITÓ ALGÚN CAMPO
                foreach ($cambios as $cambio) {
                    $this->movimientosRepository->guardarMovimiento(
                        'cuentas_corrientes', 'MODIFICACION', $usuario, $req['id'], $cambio[1], $cambio[2], $cambio[3], $cambio[0] === 'saldo' ? 'saldo' : 'responsable'
                    );
                }
            }

            DB::commit();

        } catch (\Throwable $e) {
            Log::error($e->getMessage() . $e->getTraceAsString());
            DB::rollBack();
            return response()->json(['error' => true, 'data' => $e->getMessage()]);
        }
       
        return response()->json(['error' => false]);
    }

    public function chequearSiExiste($id, $tipoCuenta) {
        if ($tipoCuenta === 'p') {
            return count(CtaCte::where([
                'proveedor_id' => $id,
                'tipo_cuenta' => $tipoCuenta,
            ])->get()->toArray()) > 0;
        } else {
            return count(CtaCte::where([
                'cliente_id' => $id,
                'tipo_cuenta' => $tipoCuenta,
            ])->get()->toArray()) > 0;
        }
    }
                
    private function buscarCamposEditados($cuenta, $req) {
        $cuenta = $cuenta->first();
        $campos = [];
        if ($req['esCliente'] ? $cuenta->cliente_id : $cuenta->proveedor_id !== $req['proveedor']) {
            //tabla, dato anterior, dato posterior, diferencia
            array_push($campos, ['proveedor', $req['esCliente'] ? $cuenta->cliente_id : $cuenta->proveedor_id, $req['proveedor'] , null]);
        }
        if ($cuenta->saldo !== $req['saldo']) {
            //tabla, dato anterior, dato posterior, diferencia
            array_push($campos, ['saldo', $cuenta->saldo, $req['saldo'], $req['saldo'] - $cuenta->saldo]);
        }
        return $campos;
    }

}
