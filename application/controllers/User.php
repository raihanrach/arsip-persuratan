<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_logged_in();

        $this->load->model('surat_model');
    }

    public function index()
    {
        $data['title'] = 'My Profile';
        $data['user'] = $this->db->get_where('user', ['username' =>
        $this->session->userdata('username')])->row_array();

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/index', $data);
            $this->load->view('templates/footer');
        } else {
            $this->db->insert('surat_keluar', ['no_surat' => $this->input->post('no_surat')]);
            $this->session->$this->session->set_flashdata('message', 
                '<div class="alert alert-success" role="alert">
                    Surat Keluar Berhasil Ditambahkan!
                </div>');
            redirect('suratKeluar');
        }
    }

    public function edit()
    {
        $data['title'] = 'Edit Profile';
        $data['user'] = $this->db->get_where('user', ['username' =>
        $this->session->userdata('username')])->row_array();

        $this->form_validation->set_rules('name', 'Nama Lengkap', 'required|trim');
        $this->form_validation->set_rules('username', 'Username', 'required|trim');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/edit', $data);
            $this->load->view('templates/footer');
        } else {
            $name = $this->input->post('name');
            $username = $this->input->post('username');

            //  Cek Jika Ada Gambar Yang Akan di Upload   
            $upload_image = $_FILES['image']['name'];

            if ($upload_image) {
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size']     = '2048';
                $config['upload_path'] = './assets/img/profile/';

                $this->load->library('upload', $config);

                if ($this->upload->do_upload('image')) {
                    $old_image = $data['user']['image'];
                    if ($old_image != 'default.jpg') {
                        unlink(FCPATH . 'assets/img/profile/' . $old_image);
                    }
                    $new_image = $this->upload->data('file_name');
                    $this->db->set('image', $new_image);
                } else {
                    echo $this->upload->dispaly_errors();
                }
            }

            $this->db->set('name', $name);
            $this->db->where('username', $username);
            $this->db->update('user');

            $this->session->set_flashdata('message', 
            '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Edit profile telah berhasil!
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
            </div>');
            redirect('user');
        }
    }

    public function changePassword()
    {
        $data['title'] = 'Change Password';
        $data['user'] = $this->db->get_where('user', ['username' =>
        $this->session->userdata('username')])->row_array();

        $this->form_validation->set_rules('current_password', 'Current Password', 'required|trim');
        $this->form_validation->set_rules('new_password1', 'New Password', 'required|trim|min_length[3]|matches[new_password2]');
        $this->form_validation->set_rules('new_password2', 'Repeat Password', 'required|trim|min_length[3]|matches[new_password1]');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/changePassword', $data);
            $this->load->view('templates/footer');
        } else {
            $current_password = $this->input->post('current_password');
            $new_password = $this->input->post('new_password1');

            if (!password_verify($current_password, $data['user']['password'])) {
                $this->session->set_flashdata('message', 
                    '<div class="alert alert-danger" role="alert">
                        Wrong current password!
                    </div>');
                redirect('user/changepassword');
            } else {
                if ($current_password == $new_password) {
                    $this->session->set_flashdata('message', 
                        '<div class="alert alert-danger" role="alert">
                            New password cannot be the same as current password!
                        </div>');
                    redirect('user/changepassword');
                } else {
                    //password sudah oke
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

                    $this->db->set('password', $password_hash);
                    $this->db->where('username', $this->session->userdata('username'));
                    $this->db->update('user');

                    $this->session->set_flashdata('message', 
                        '<div class="alert alert-success" role="alert">
                            Password changed!
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true"> &times;</button>
                        </div>');
                    redirect('user/changepassword');
                }
            }
        }
    }

    public function berandaUser()
    {
        $data['title'] = 'Beranda User';
        $data['user'] = $this->db->get_where('user', ['username' =>
        $this->session->userdata('username')])->row_array();
    
        $data['suratMasuk'] = $this->surat_model->countSm();
        $data['suratKeluar'] = $this->surat_model->countSk();
        $data['disposisi'] = $this->surat_model->disposisi();
        // $data['users'] = $this->surat_model->user();
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbarBeranda', $data);
        $this->load->view('user/berandaUser', $data);
        $this->load->view('templates/footer');
    }

    // SURAT MASUK
    public function suratMasuk()
    {
        $data['title'] = 'Surat Masuk';
        $data['user'] = $this->db->get_where('user', ['username' =>
        $this->session->userdata('username')])->row_array();
        $query = "SELECT * FROM surat_masuk as sm LEFT JOIN disposisi as d ON sm.id = d.id_suratMasuk ORDER BY sm.id";
        $data['surat_masuk'] = $this->db->query($query)->result_array();

        $this->form_validation->set_rules('no_surat', 'No Surat', 'required');
        $this->form_validation->set_rules('tgl_suratMasuk', 'Tanggal Surat', 'required');
        $this->form_validation->set_rules('pengirim', 'Pengirim', 'required');
        $this->form_validation->set_rules('penerima', 'Penerima', 'required');
        $this->form_validation->set_rules('perihal', 'Perihal', 'required');
        $this->form_validation->set_rules('disposisi', 'Disposisi', 'required');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/suratMasuk', $data);
            $this->load->view('templates/footer');
        } else {
            $data = [
                'no_surat' => $this->input->post('no_surat'),
                'tgl_suratMasuk' => $this->input->post('tgl_suratMasuk'),
                'pengirim' => $this->input->post('pengirim'),
                'penerima' => $this->input->post('penerima'),
                'no_surat' => $this->input->post('no_surat'),
                'perihal' => $this->input->post('perihal'),
                'disposisi' => $this->input->post('disposisi')
            ];
            $this->db->insert('surat_masuk', $data);
            $this->session->set_flashdata('message', 
                '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        Surat Baru Berhasil Ditambah!
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button> 
                </div>');
            redirect('user/suratMasuk');
        }
    }

   public function tambahSuratMasuk()
    {
        $data['title'] = 'Form Tambah Surat Masuk';
        $data['user'] = $this->db->get_where('user', ['username' =>
        $this->session->userdata('username')])->row_array();

        $data['surat_masuk'] = $this->db->get('surat_masuk')->result_array();

        $this->form_validation->set_rules('tgl', 'Tanggal', 'required');
        $this->form_validation->set_rules('no_agenda', 'No. Agenda', 'required');
        $this->form_validation->set_rules('no_surat', 'No Surat', 'required');
        $this->form_validation->set_rules('tgl_suratMasuk', 'Tanggal Surat', 'required');
        $this->form_validation->set_rules('pengirim', 'Pengirim', 'required');
        $this->form_validation->set_rules('penerima', 'Penerima', 'required');
        $this->form_validation->set_rules('perihal', 'Perihal', 'required');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/tambahSuratMasuk', $data);
            $this->load->view('templates/footer');
        } else {
            $tgl = $this->input->post('tgl');
            $no_agenda = $this->input->post('no_agenda');
            $no_surat = $this->input->post('no_surat');
            $tgl_suratMasuk = $this->input->post('tgl_suratMasuk');
            $pengirim = $this->input->post('pengirim');
            $penerima = $this->input->post('penerima');
            $no_surat = $this->input->post('no_surat');
            $perihal = $this->input->post('perihal');
            // $disposisi = $this->input->post('disposisi');
            $lampiran = $_FILES['lampiran']['name'];

            if ($lampiran = '') {
            } else {
                $config['allowed_types'] = 'pdf|docx|doc';
                $config['max_size']     = '5048';
                $config['upload_path'] = './uploads/';

                $this->load->library('upload', $config);

                if (!$this->upload->do_upload('lampiran')) {
                    echo "Lampiran gagal di-upload";
                } else {
                    $lampiran = $this->upload->data('file_name');
                }
            }

            $data = array(
                'tgl' => $tgl,
                'no_agenda' => $no_agenda,
                'no_surat' => $no_surat,
                'tgl_suratMasuk' => $tgl_suratMasuk,
                'pengirim' => $pengirim,
                'penerima' => $penerima,
                'no_surat' => $no_surat,
                'perihal' => $perihal,
                // 'disposisi' => $disposisi,
                'lampiran' => $lampiran
            );

            $this->db->insert('surat_masuk', $data);
            $this->session->set_flashdata(
                'message',
                '<div class="alert alert-success alert-dismissible fade show" role="alert">
            Surat Masuk Berhasil Ditambah!
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>'
            );
            redirect('user/suratMasuk');
        }
    }

    public function DetailsuratMasuk($id)
    {
        $data['title'] = 'Detail Surat Masuk';
        $data['user'] = $this->db->get_where('user', ['username' =>
        $this->session->userdata('username')])->row_array();
        // $query = "SELECT * FROM surat_masuk as sm LEFT JOIN disposisi as d ON sm.id = d.id_suratMasuk ORDER BY sm.id";
        // $data['surat_masuk'] = $this->db->query($query)->result_array();

        $data['detail'] = $this->surat_model->ambil_id_suratSM($id);
        
        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/detailSM', $data);
            $this->load->view('templates/footer');
        }
    }

    public function DetailsuratKeluar($id_suratKeluar)
    {
        $data['title'] = 'Detail Surat Keluar';
        $data['user'] = $this->db->get_where('user', ['username' =>
        $this->session->userdata('username')])->row_array();
        // $query = "SELECT * FROM surat_masuk as sm LEFT JOIN disposisi as d ON sm.id = d.id_suratMasuk ORDER BY sm.id";
        // $data['surat_masuk'] = $this->db->query($query)->result_array();

        $data['detail'] = $this->surat_model->ambil_id_suratSK($id_suratKeluar);
        
        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/detailSK', $data);
            $this->load->view('templates/footer');
        }
    }

    public function updateSM($id)
    {
        $data['title'] = 'Form Update Surat Masuk';
        $data['user'] = $this->db->get_where('user', ['username' =>
        $this->session->userdata('username')])->row_array();
        $data['surat_masuk'] = $this->surat_model->getsuratByID($id);

        $this->form_validation->set_rules('tgl', 'Tanggal', 'required');
        $this->form_validation->set_rules('no_agenda', 'No. Agenda', 'required');
        $this->form_validation->set_rules('no_surat', 'no_surat', 'required');
        $this->form_validation->set_rules('tgl_suratMasuk', 'tgl_suratMasuk', 'required');
        $this->form_validation->set_rules('pengirim', 'pengirim', 'required');
        $this->form_validation->set_rules('penerima', 'penerima', 'required');
        $this->form_validation->set_rules('perihal', 'perihal', 'required');

        if ($this->form_validation->run() == FALSE) {

            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/updateSM', $data);
            $this->load->view('templates/footer');
        } else {
            $tgl = $this->input->post('tgl');
            $no_agenda = $this->input->post('no_agenda');
            $no_surat = $this->input->post('no_surat');
            $tgl_suratMasuk = $this->input->post('tgl_suratMasuk');
            $pengirim = $this->input->post('pengirim');
            $penerima = $this->input->post('penerima');
            $perihal = $this->input->post('perihal');

            $data = array(
                'tgl' => $tgl,
                'no_agenda' => $no_agenda,
                'no_surat' => $no_surat,
                'tgl_suratMasuk' => $tgl_suratMasuk,
                'pengirim' => $pengirim,
                'penerima' => $penerima,
                'no_surat' => $no_surat,
                'perihal' => $perihal
                // 'lampiran' => $lampiran
            );

            $this->db->where('id', $this->input->post('id'));
            $this->db->update('surat_masuk', $data);

            $this->session->set_flashdata(
                'message',
                '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Surat Masuk Berhasil Diubah!
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>'
            );
            redirect('user/suratMasuk', 'refresh');
        }
    }
    // END SURAT MASUK

    // START SURAT KELUAR
    public function suratKeluar()
    {
        $data['title'] = 'Surat Keluar';
        $data['user'] = $this->db->get_where('user', ['username' =>
        $this->session->userdata('username')])->row_array();

        $data['surat_keluar'] = $this->db->get('surat_keluar')->result_array();
        $this->form_validation->set_rules('no_surat', 'No Surat', 'required');
        $this->form_validation->set_rules('tgl_suratKeluar', 'Tanggal Surat', 'required');
        $this->form_validation->set_rules('pengirim', 'Pengirim', 'required');
        $this->form_validation->set_rules('penerima', 'Penerima', 'required');
        $this->form_validation->set_rules('perihal', 'Perihal', 'required');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/suratKeluar', $data);
            $this->load->view('templates/footer');
        } else {
            $data = [
                'no_surat' => $this->input->post('no_surat'),
                'tgl_suratKeluar' => $this->input->post('tgl_suratKeluar'),
                'pengirim' => $this->input->post('pengirim'),
                'penerima' => $this->input->post('penerima'),
                'perihal' => $this->input->post('perihal')
            ];
            $this->db->insert('surat_keluar', $data);
            $this->session->set_flashdata('message', 
                '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    Surat disposisi berhasil di update!
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                </div>');
            redirect('user/suratKeluar');
        }
    }

    public function deleteSM($id)
    {
        $this->surat_model->hapus_suratMasuk($id);
        $this->session->set_flashdata('message',
            '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Surat Masuk Berhasil Dihapus!
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>');
        redirect('user/suratMasuk');
    }

    public function tambahSuratKeluar()
    {
        $data['title'] = 'Form Tambah Surat Keluar';
        $data['user'] = $this->db->get_where('user', ['username' =>
        $this->session->userdata('username')])->row_array();

        $data['surat_keluar'] = $this->db->get('surat_keluar')->result_array();
        $this->form_validation->set_rules('no_surat', 'No Surat', 'required');
        $this->form_validation->set_rules('tgl_suratKeluar', 'Tanggal Surat', 'required');
        $this->form_validation->set_rules('pengirim', 'Pengirim', 'required');
        $this->form_validation->set_rules('penerima', 'Penerima', 'required');
        $this->form_validation->set_rules('perihal', 'Perihal', 'required');
        $this->form_validation->set_rules('lampiran', 'File', 'trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/tambahSuratKeluar', $data);
            $this->load->view('templates/footer');
        } else {
            $tgl = $this->input->post('tgl');
            $no_agenda = $this->input->post('no_agenda');
            $no_surat = $this->input->post('no_surat');
            $tgl_suratKeluar = $this->input->post('tgl_suratKeluar');
            $pengirim = $this->input->post('pengirim');
            $penerima = $this->input->post('penerima');
            $perihal = $this->input->post('perihal');
            $lampiran = $_FILES['lampiran']['name'];

            if ($lampiran = '') {
            } else {
                $config['allowed_types'] = 'pdf|docx|doc';
                $config['max_size']     = '5048';
                $config['upload_path'] = './uploads/';

                $this->load->library('upload', $config);

                if (!$this->upload->do_upload('lampiran')) {
                    echo "Lampiran gagal di-upload";
                } else {
                    $lampiran = $this->upload->data('file_name');
                }
            }

            $data = array(
                'tgl' => $tgl,
                'no_agenda' => $no_agenda,
                'no_surat' => $no_surat,
                'tgl_suratKeluar' => $tgl_suratKeluar,
                'pengirim' => $pengirim,
                'penerima' => $penerima,
                'perihal' => $perihal,
                'lampiran' => $lampiran
            );

            $this->db->insert('surat_keluar', $data);

            $this->session->set_flashdata(
                'message',
                '<div class="alert alert-success alert-dismissible fade show" role="alert">
            Tambah Surat Keluar berhasil!
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        </div>'
            );
            redirect('user/suratKeluar');
        }
    }

    public function deleteSK($id)
    {
        $this->surat_model->hapus_suratKeluar($id);

        $this->session->set_flashdata('message',
            '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Surat Keluar Berhasil Dihapus!
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>');
        redirect('user/suratKeluar');
    }
    
    public function updateSuratKeluar($id)
    {
        $data['title'] = 'Form Update Surat Keluar';
        $data['user'] = $this->db->get_where('user', ['username' =>
        $this->session->userdata('username')])->row_array();
        $data['surat_keluar'] = $this->surat_model->getsuratKeluarByID($id);

        $this->form_validation->set_rules('tgl', 'Tanggal', 'required');
        $this->form_validation->set_rules('no_agenda', 'no_agenda', 'required');
        $this->form_validation->set_rules('no_surat', 'no_surat', 'required');
        $this->form_validation->set_rules('tgl_suratKeluar', 'tgl_suratKeluar', 'required');
        $this->form_validation->set_rules('pengirim', 'pengirim', 'required');
        $this->form_validation->set_rules('penerima', 'penerima', 'required');
        $this->form_validation->set_rules('perihal', 'perihal', 'required');

        if ($this->form_validation->run() == FALSE) {

            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/updateSuratKeluar', $data);
            $this->load->view('templates/footer');
        } else {
            $tgl = $this->input->post('tgl');
        $no_agenda = $this->input->post('no_agenda');
        $no_surat = $this->input->post('no_surat');
        $tgl_suratKeluar = $this->input->post('tgl_suratKeluar');
        $pengirim = $this->input->post('pengirim');
        $penerima = $this->input->post('penerima');
        $perihal = $this->input->post('perihal');

            $data = array(
                'tgl' => $tgl,
                'no_agenda' => $no_agenda,
                'no_surat' => $no_surat,
                'tgl_suratKeluar' => $tgl_suratKeluar,
                'pengirim' => $pengirim,
                'penerima' => $penerima,
                'perihal' => $perihal
                // 'lampiran' => $lampiran
            );

            $this->db->where('id_suratKeluar', $this->input->post('id_suratKeluar'));
            $this->db->update('surat_keluar', $data);

            $this->session->set_flashdata(
                'message',
                '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Surat Masuk Berhasil Diubah!
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>'
            );
            redirect('user/suratKeluar', 'refresh');
        }
        //     $this->surat_model->updateDataSuratKeluar();
        //     $this->session->set_flashdata(
        //         'message',
        //             '<div class="alert alert-success alert-dismissible fade show" role="alert">
        //                 Surat Keluar Berhasil Update!
        //                     <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        //                         <span aria-hidden="true">&times;</span>
        //                     </button>
        //             </div>'
        //     );
        //     redirect('user/suratKeluar', 'refresh');
        // }
    }
    // END SURAT KELUAR

    // START DISPOSISI
    public function Disposisi()
    {
        $data['title'] = 'Disposisi';
        $data['user'] = $this->db->get_where('user', ['username' =>
        $this->session->userdata('username')])->row_array();

        $this->db->select('*')
            ->from('surat_masuk as sk')->join('disposisi as d', 'sk.id = d.id_suratMasuk');
        $data['disposisi'] = $this->db->get()->result_array();

        $this->form_validation->set_rules('surat_dari', 'Surat Dari', 'required');
        $this->form_validation->set_rules('tgl_surat', 'Tanggal Surat', 'required');
        $this->form_validation->set_rules('no_surat', 'No Surat', 'required');
        $this->form_validation->set_rules('diterima_tgl', 'Diterima Tanggal', 'required');
        $this->form_validation->set_rules('no_agenda', 'Nomor Agenda', 'required');
        $this->form_validation->set_rules('perihal', 'Perihal', 'required');
        $this->form_validation->set_rules('diteruskan_kepada', 'Diteruskan Kepada', 'required');
        $this->form_validation->set_rules('isi_disposisi', 'Isi Disposisi', 'required');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/disposisi', $data);
            $this->load->view('templates/footer');
        } else {
            $data = [
                'surat_dari' => $this->input->post('surat_dari'),
                'tgl_surat' => $this->input->post('tgl_surat'),
                'no_surat' => $this->input->post('no_surat'),
                'diterima_tgl' => $this->input->post('diterima_tgl'),
                'no_agenda' => $this->input->post('no_agenda'),
                'perihal' => $this->input->post('perihal'),
                'diteruskan_kepada' => $this->input->post('diteruskan_kepada'),
                'isi_disposisi' => $this->input->post('isi_disposisi')

            ];
            $this->db->insert('disposisi', $data);
            $this->session->set_flashdata(
                'message',
                '<div class="alert alert-success alert-dismissible fade show" role="alert">
            Surat Disposisi Berhasil Ditambah!
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>'
            );
            redirect('user/disposisi');
        }
    }

    public function tambahDisposisi()
    {
        $data['title'] = 'Form Tambah Data Disposisi';
        $data['user'] = $this->db->get_where('user', ['username' =>
        $this->session->userdata('username')])->row_array();

        $data['disposisi'] = $this->db->get('surat_masuk')->result_array();
        $this->form_validation->set_rules('id_suratMasuk', 'No Surat', 'required');
        $this->form_validation->set_rules('diterima_tgl', 'Diterima Tanggal', 'required');
        $this->form_validation->set_rules('diteruskan_kepada', 'Diteruskan Kepada', 'required');
        $this->form_validation->set_rules('isi_disposisi', 'Isi Disposisi', 'required');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/tambahDisposisi', $data);
            $this->load->view('templates/footer');
        } else {
            $data = [
            
                'id_suratMasuk' => $this->input->post('id_suratMasuk'),
                'diterima_tgl' => $this->input->post('diterima_tgl'),
                'diteruskan_kepada' => $this->input->post('diteruskan_kepada'),
                'isi_disposisi' => $this->input->post('isi_disposisi')

            ];
            $this->db->insert('disposisi', $data);
            $this->session->set_flashdata(
                'message',
                '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    Surat disposisi berhasil ditambah!
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                </div>'
            );
            redirect('user/disposisi');
        }
    }

    public function deleteDisposisi($id)
    {
        $this->surat_model->hapus_disposisi($id);
        
        $this->session->set_flashdata('message', 
            '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Surat disposisi berhasil dihapus!
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>');
        redirect('user/disposisi');
    }

    public function detail($id_disposisi)
    {
        $data['title'] = 'Detail Surat Disposisi';
        $data['user'] = $this->db->get_where('user', ['username' =>
        $this->session->userdata('username')])->row_array();

        $data['detail'] = $this->surat_model->ambil_id_disposisi($id_disposisi);

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/detailDisposisi', $data);
        $this->load->view('templates/footer');
    }

    public function updateDisposisi($id_disposisi)
    {
        $data['title'] = 'Form Update Disposisi';
        $data['user'] = $this->db->get_where('user', ['username' =>
        $this->session->userdata('username')])->row_array();

        $data['isi_dispo'] = ['Tindak Lanjut', 'Untuk Diketahui', 'UMP', 'Hadir'];

        $data['suratMasuk'] = $this->db->get('surat_masuk')->result_array();
        $data['disposisi'] = $this->surat_model->getDisposisiByID($id_disposisi);

        $this->form_validation->set_rules('id_suratMasuk', 'No Surat', 'required');
        $this->form_validation->set_rules('diterima_tgl', 'Diterima Tanggal', 'required');
        $this->form_validation->set_rules('diteruskan_kepada', 'Diteruskan Kepada', 'required');
        $this->form_validation->set_rules('isi_disposisi', 'Isi Disposisi', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/updateDisposisi', $data);
            $this->load->view('templates/footer');
        } else {
            $this->surat_model->updateDataDisposisi();
            $this->session->set_flashdata(
                'message',
                '<div class="alert alert-success alert-dismissible fade show" role="alert">
            Surat Disposisi Berhasil Update!
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>'
            );
            redirect('user/disposisi', 'refresh');
        }
    }

    // END DISPOSISI

    // START USERS
    public function users(){
        $data['title'] = 'List akun yang terdaftar';
        $data['user'] = $this->db->get_where('user', ['username' =>
        $this->session->userdata('username')])->row_array();

        // $this->db->select('*')->from('user as u')->join('user_role as ur', 'u.role_id = ur.id');
        $data['users'] = $this->db->get('user')->result_array();
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/users', $data);
        $this->load->view('templates/footer');
    }

    public function updateUsers($id){
        $data['title'] = 'Form Update Users';
        $data['user'] = $this->db->get_where('user', ['username' =>
        $this->session->userdata('username')])->row_array();

        // Start Konfigurasi model users
        $where = array('id' => $id);
        $data['users'] = $this->surat_model->edit_data_users($where,'user')->result();
        // End Konfigurasi model disposisi

            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/updateUsers', $data);
            $this->load->view('templates/footer');
    }

    public function update_aksi_users()
    {
        $id = $this->input->post('id');
        $name = $this->input->post('name');
        $username = $this->input->post('username');
        $image = $this->input->post('image');
        // $password = $this->input->post('password');
        $role_id = $this->input->post('role_id');
        $is_active = $this->input->post('is_active');
        // $date_created = $this->input->post('date_created');

        $data = array(
            'name' => $name,
            'username' => $username,
            'image' => $image,
            // 'password' => $password,
            'role_id' => $role_id,
            'is_active' => $is_active,
            // 'date_created' => $date_created
        );

        $where = array (
            'id' => $id
        );

        $this->surat_model->update_data_users($where, $data, 'user');
        $this->session->set_flashdata('message', 
            '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Data User berhasil di update!
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
            </div>');
        redirect('user/users');
    }

    public function deleteUsers($id)
    {
        $this->surat_model->hapus_users($id);
        $this->session->set_flashdata('message', 
            '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Data User berhasil dihapus!
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
            </div>');
        redirect('user/users');
    }

    public function detailUsers($id){
        $data['title'] = 'Detail User';
        $data['user'] = $this->db->get_where('user', ['username' =>
        $this->session->userdata('username')])->row_array();

        $data['users'] = $this->db->get('user')->result_array();      
        $data['detail'] = $this->surat_model->ambil_id_user($id);

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/detailUsers', $data);
        $this->load->view('templates/footer');
    }

    public function print($id_disposisi){
        $data['printDisposisi'] = $this->surat_model->ambil_id_disposisi($id_disposisi);
        $this->load->view('user/print_disposisi', $data);
    }

    // public function print($id_disposisi){
    //     $data['printDisposisi'] = $this->surat_model->ambil_id_disposisi($id_disposisi);
    //     $this->load->view('user/print_disposisi', $data);
    // }

    public function update_aksi()
    {

        $id = $this->input->post('id_disposisi');
        // $surat_dari = $this->input->post('surat_dari');
        // $tgl_surat = $this->input->post('tgl_surat');
        // $no_surat = $this->input->post('no_surat');
        $id_suratMasuk = $this->input->post('id_suratMasuk');
        $diterima_tgl = $this->input->post('diterima_tgl');
        // $no_agenda = $this->input->post('no_agenda');
        // $perihal = $this->input->post('perihal');
        $diteruskan_kepada = $this->input->post('diteruskan_kepada');
        $isi_disposisi = $this->input->post('isi_disposisi');

        $data = array(

            // 'surat_dari' => $surat_dari,
            // 'tgl_surat' => $tgl_surat,
            // 'no_surat' => $no_surat,
            'id_suratMasuk' => $id_suratMasuk,
            'diterima_tgl' => $diterima_tgl,
            // 'no_agenda' => $no_agenda,
            // 'perihal' => $perihal,
            'diteruskan_kepada' => $diteruskan_kepada,
            'isi_disposisi' => $isi_disposisi
        );

        $where = array(
            'id_disposisi' => $id
        );

        $this->surat_model->update_data($where, $data, 'disposisi');
        $this->session->set_flashdata(
            'message',
            '<div class="alert alert-success alert-dismissible fade show" role="alert">
            Surat disposisi berhasil di update!
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>'
        );
        redirect('user/disposisi');
    }

}
// END DISPOSISI