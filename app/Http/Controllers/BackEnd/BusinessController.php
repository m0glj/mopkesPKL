<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Business;
use App\Province;
use App\Sector;
use App\Agreement;
use App\District;
use App\Village;
use DB;
use PDF;

class BusinessController extends Controller
{
    public function __construct(){
        $this->kecparent = District::where('regency_id', 3328)->orderBy('name', 'asc')->pluck('name', 'id')->prepend('Pilih Kecamatan', 0);
        $this->desparent = Village::where('name', 0)->orderBy('name', 'asc')->pluck('name', 'id')->prepend('Pilih Desa', 0);
        $this->sectorparent = Sector::where('sector_name', 0)->orderBy('sector_name', 'asc')->pluck('sector_name', 'id')->prepend('Pilih Sektor', 0);
        // $this->nikparent = Agreement::leftJoin('business', 'nik', '=', 'nik_id')->whereNull('nik_id')->pluck('nik', 'nik')->prepend('NIK', '');
        $this->nikparent = Agreement::get(['id', 'nik'])->pluck('nik', 'nik')->prepend('NIK', '');
        $this->provparent = Province::where('name', 0)->orderBy('name', 'asc')->pluck('name', 'id')->prepend('Pilih Provinsi', 0);
    }

    public function index()
    {
        $business = Business::all();
        if (\Request::ajax()) {
            $view = view('backend.business.index', compact('business'))->renderSections();
            return response()->json([
                'content' => $view['content'],
                'css' => $view['css'],
                'js' => $view['js'],
            ]);
        }
        return view('backend.business.index', compact('business'))->render();
    }

    public function create()
    {
        $kecparent = $this->kecparent;
        $desparent = $this->desparent;
        $sectorparent = $this->sectorparent;
        $nikparent = $this->nikparent;
        if (\Request::ajax()) {
            $view = view('backend.business.create', compact('kecparent', 'desparent', 'sectorparent', 'nikparent'))->renderSections();
            return response()->json([
                'content' => $view['content'],
                'css' => $view['css'],
                'js' => $view['js'],
            ]);
        }
        return view('backend.business.create', compact('kecparent', 'desparent', 'sectorparent', 'nikparent'))->render();
    }

    public function store(Request $request)
    {

        // dd($request->all());

        $datakelompok = Agreement::where('nik', $request->nik_id)->first();
        // dd($datakelompok->nik);
        // $id_nik = Agreement::where('nik', $request->nik_id)->first();
        // dd($id_nik->id);

        $databusiness = new Business;
        $databusiness->nik_id = $request->nik_id;
        $databusiness->lapak_kec = $request->lapak_kec;
        $databusiness->lapak_desa = $request->lapak_desa;
        $databusiness->lapak_addr = $request->lapak_addr;
        $databusiness->sector_id = $request->sector_id;
        $databusiness->Business_specific = $request->Business_specific;
        $databusiness->waktu_jual = $request->waktu_jual;
        if($datakelompok->community_id == null){
            $databusiness->status_kelompok = "Tidak";
        }else {
            $databusiness->status_kelompok = "Ya";
        }
        $databusiness->is_active = 1;
        $databusiness->community_id = $datakelompok->community_id;
        // $databusiness->save();

        // $url = url()->current();
        // $fixurl = str_replace( array( $id ), ' ', $url);

        // return redirect($fixurl)->with('success','Data Berhasil Disimpan');

        $status = $databusiness->save();
        if ($status) {
            $data['status'] = true;
            $data['message'] = "Data berhasil disimpan!!!";
        } else {
            $data['status'] = false;
            $data['message'] = "Data gagal disimpan!!!";
        }
        return response()->json(['code' => 200,'data' => $data], 200);
    }

    public function show($id)
    {
        $business = Business::findOrFail($id);

        return response()->json($business);
    }

    public function edit($id)
    {
        $business = Business::findOrFail($id);
        $provparent = $this->provparent;
        $kecparent = $this->kecparent;
        $sectorparent = $this->sectorparent;
        $nikparent = $this->nikparent;
        if (\Request::ajax()) {
            $view = view('backend.business.edit', compact('business', 'kecparent', 'provparent', 'sectorparent', 'nikparent'))->renderSections();
            return response()->json([
                'content' => $view['content'],
                'css' => $view['css'],
                'js' => $view['js'],
            ]);
        }
        return view('backend.business.edit', compact('business', 'kecparent', 'provparent', 'sectorparent', 'nikparent'));
    }

    public function update(Request $request, $id)
    {
        $business = Business::findOrFail($id);
        // dd($business);

        $business->update($request->all());

        return redirect(url('/admin/business'))->with('success','Data Berhasil Disimpan');
    }

    public function delete($id)
    {
        $data = Business::find($id);
        $data->delete();
        return back()->with('warning','Data Berhasil Dihapus');
    }

    public function generate($id)
    {

        $pedagang = DB::table('business')
                ->join('agreements', 'business.nik_id', '=', 'agreements.nik')
                ->where('business.id', '=', $id)
                ->get();

                // dd($pedagang);

        return view('backend.business.qrcode', ['pedagang' => $pedagang]);
    }

    public function qrall(Request $request)
    {
        $ids = $request->generate;
        if ($ids == null){
            echo ("<h2>DATA KOSONG</h2>");
        }
        $generate = Business::findOrFail($ids);
        // dd($generate);

        return view('backend.business.qrcode', ['pedagang' => $generate]);

    }

    // public function cetak_pdf(Request $request)
    // {
    //     $ids = $request->generate;
    //     $generate = Business::findOrFail($ids);

    //     $pdf = PDF::loadview('backend.business.qrcode', ['pedagang' => $generate]);
    // 	return $pdf->download('QRcode-pdf');
    // }


}
