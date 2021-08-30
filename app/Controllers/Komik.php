<?php

namespace App\Controllers;

use App\Models\KomikModel;

class Komik extends BaseController
{
    protected $komikModel;

    public function __construct()
    {
        // $komikmodel =  new \App\Models\KomikModel();
        $this->komikModel = new KomikModel();
    }

    public function index()
    {

        // dd($this->komikModel->getKomik());

        $data = [
            'title' => 'Komik | WebProgrammingUnpas',
            'komik' => $this->komikModel->getKomik()
        ];

        return view('komik/index', $data);
    }
    public function detail($slug)
    {
        $data = [
            'title' => 'Detail Komik | WebProgrammingUnpas',
            'komik' => $this->komikModel->getKomik($slug),
        ];

        // jika komik tidak ada di table
        if (empty($data['komik'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('judul komik ' . $slug . ' tidak  di temukan');
        }

        return view('komik/detail', $data);
    }
    public function create()
    {
        $data = [
            'title' => 'Create Komik | WebProgrammingUnpas',
            'validation' => \Config\Services::validation(),
        ];

        return view('komik/create', $data);
    }
    public function save()
    {

        // validasi input
        if (!$this->validate([
            'judul' => [
                'rules' => 'required|is_unique[komik.judul]',
                'errors' => [
                    'required' => '{field} Komik Harus Diisi.',
                    'is_unique' => '{field} Komik Sudah Terdaftar'
                ]
            ],
            'sampul' => [
                'rules' => 'max_size[sampul,1024]|is_image[sampul]|mime_in[sampul,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'max_size' => 'ukuran gambar sampul terlalu besar',
                    'is_image' => 'yang anda pilih bukan gambar',
                    'mime_in' => 'Yand anda pilih bukan gambar',
                ]
            ]

        ])) {
            // $validation = \Config\Services::validation();
            // return redirect()->to('/komik/create')->withInput()->with('validation', $validation);
            return redirect()->to('/komik/create')->withInput();
        }

        // ambil gambar
        $fileSampul = $this->request->getFile('sampul');

        // apakah tidak ada file gambar yang di upload
        if ($fileSampul->getError() == 4) {
            $namaSampul = 'default.png';
        } else {
            // generate nama file random
            $namaSampul = $fileSampul->getRandomName();

            // pindahkan file ke folder img
            $fileSampul->move('img', $namaSampul);


            // ambil nama file
            // $namaSampul = $fileSampul->getName();
        }




        $slug = url_title($this->request->getVar('judul'), '-', 'true');

        $this->komikModel->save([
            'judul' => $this->request->getVar('judul'),
            'slug' => $slug,
            'penulis' => $this->request->getVar('penulis'),
            'penerbit' => $this->request->getVar('penerbit'),
            'sampul' => $namaSampul
        ]);

        session()->setFlashdata('pesan', 'Data Berhasil Di Tambahkan');

        return redirect()->to('/komik');
    }
    public function delete($id)
    {
        // cari gambar berdasarkan ID
        $komik = $this->komikModel->find($id);


        // hapus gambar
        if ($komik['sampul'] != 'default.png') {
            unlink('img/' . $komik['sampul']);
        }

        $this->komikModel->delete($id);

        session()->setFlashdata('pesan', 'Data Berhasil Di Hapus');


        return redirect()->to('/komik');
    }
    public function edit($slug)
    {
        $data = [
            'title' => 'Edit Komik | WebProgrammingUnpas',
            'validation' => \Config\Services::validation(),
            'komik' => $this->komikModel->getKomik($slug),
        ];

        return view('komik/edit', $data);
    }

    public function update($id)
    {
        // cek judul
        $komikLama = $this->komikModel->getKomik($this->request->getVar('slug'));

        if ($komikLama['judul'] == $this->request->getVar('judul')) {
            $rule_judul = 'required';
        } else {
            $rule_judul = 'required|is_unique[komik.judul]';
        }

        if (!$this->validate([
            'judul' => [
                'rules' => $rule_judul,
                'errors' => [
                    'required' => '{field} Komik Harus Diisi.',
                    'is_unique' => '{field} Komik Sudah Terdaftar'
                ]
            ],
            'sampul' => [
                'rules' => 'max_size[sampul,1024]|is_image[sampul]|mime_in[sampul,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'max_size' => 'ukuran gambar sampul terlalu besar',
                    'is_image' => 'yang anda pilih bukan gambar',
                    'mime_in' => 'Yand anda pilih bukan gambar',
                ]
            ]
        ])) {
            // $validation = \Config\Services::validation();
            return redirect()->to('/komik/edit/' . $this->request->getVar('slug'))->withInput();
        }


        $fileSampul = $this->request->getFile('sampul');

        // cek gambar, apakah tetap gambar yang lama
        if ($fileSampul->getError() == 4) {
            $namaSampul = $this->request->getVar('sampulLama');
        } else {
            $namaSampul = $fileSampul->getRandomName();
            // upload gambar
            $fileSampul->move('img', $namaSampul);
            // hapus Gambar
            unlink('img/' . $this->request->getVar('sampulLama'));
        }


        $slug = url_title($this->request->getVar('judul'), '-', 'true');

        $this->komikModel->save([
            'id' => $id,
            'judul' => $this->request->getVar('judul'),
            'slug' => $slug,
            'penulis' => $this->request->getVar('penulis'),
            'penerbit' => $this->request->getVar('penerbit'),
            'sampul' => $namaSampul
        ]);

        session()->setFlashdata('pesan', 'Data Berhasil Di Ubah');

        return redirect()->to('/komik');
    }
}
