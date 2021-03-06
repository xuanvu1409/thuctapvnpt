<?php

namespace App\Controller\Admin;

use Core\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use App\Models\DiaChinh\Province;
use App\Models\DiaChinh\District;
use App\Models\DiaChinh\Ward;

class DiaChinhController extends Controller {

    public function getTinh() {
        $data = Province::withTrashed()->all();

        return response()->json($data);
    }

    public function getHuyen() {
        $id = request()->id ?? '01';

        $data = District::withTrashed()->where(['province_id' => $id])->get();
        return response()->json($data);
    }

    public function getXa() {
        $id = request()->id ?? '001';

        $data = Ward::withTrashed()->where(['district_id' => $id])->get();
        return response()->json($data);
    }

    public function getDiaChi() {
        $id = request()->id ?? '00001';

        $ward = Ward::withTrashed()->find($id);

        $district = District::withTrashed()->find($ward->district_id);

        $province = Province::withTrashed()->find($district->province_id);

        $list_province = Province::withTrashed()->all();
        $list_district = District::withTrashed()->where(['province_id' => $province->id])->get();
        $list_ward = Ward::withTrashed()->where(['district_id' => $district->id])->get();

        return response()->json([
            'ward' => $ward,
            'district' => $district,
            'province' => $province,
            'list_province' => $list_province,
            'list_district' => $list_district,
            'list_ward' => $list_ward,
        ]);
    }
    
    public function get_tinh() {
        
        if(request()->has('id')) {
            $data = Province::withTrashed()->find(request()->id);
        } else {
            $data = Province::withTrashed()->all();
        }
        
        return response()->json($data);
    }

    public function pagination_tinh() {
        $first = request()->first ?? 0;
        $row = request()->row ?? 10;

        $data = Province::withTrashed()->offset($first)->limit($row)->get();

        return response()->json([
            'total' => Province::withTrashed()->count(),
            'data' => $data,
        ]);
    }
    
    public function get_huyen() {

        if(request()->has('id')) {
            $data = District::withTrashed()->find(request()->id);
        } else if(request()->has('province_id')) {
            $data = District::withTrashed()->where(['province_id' => request()->province_id])->get();
        } else {
            $data = District::withTrashed()->all();
        }
        
        return response()->json($data);
    }

    public function pagination_huyen() {
        $first = request()->first ?? 0;
        $row = request()->row ?? 10;
        $province_id = request()->province_id ?? null;

        if($province_id) {
            $total = District::withTrashed()->where(['province_id' => $province_id])->count();
            $data = District::withTrashed()->where(['province_id' => $province_id])->offset($first)->limit($row)->get();
        } else {
            $total = District::withTrashed()->count();
            $data = District::withTrashed()->offset($first)->limit($row)->get();
        }

        return response()->json([
            'total' => $total,
            'data' => $data,
        ]);
    }
    
    public function get_xa() {

        if(request()->has('id')) {
            $data = Ward::withTrashed()->find(request()->id);
        } else if(request()->has('district_id')) {
            $data = Ward::withTrashed()->where(['district_id' => request()->district_id])->get();
        } else {
            $data = Ward::withTrashed()->all();
        }
        
        return response()->json($data);
    }

    public function pagination_xa() {
        $first = request()->first ?? 0;
        $row = request()->row ?? 10;
        $district_id = request()->district_id ?? null;

        if($district_id) {
            $total = Ward::withTrashed()->where(['district_id' => $district_id])->count();
            $data = Ward::withTrashed()->where(['district_id' => $district_id])->offset($first)->limit($row)->get();
        } else {
            $total = Ward::withTrashed()->count();
            $data = Ward::withTrashed()->offset($first)->limit($row)->get();
        }

        return response()->json([
            'total' => $total,
            'data' => $data,
        ]);
    }

    public function create_tinh() {
        
        validator()->validate([
            'name' => [
                'required' => 'T??n t???nh, th??nh ph??? kh??ng ???????c ????? tr???ng',
                'max:45' => 'T??n t???nh, th??nh ph??? kh??ng qu?? 45 k?? t???',
                'unique:province' => 'T??n t???nh, th??nh ph??? ???? t???n t???i',
            ],
            'type' => [
                'required' => 'Lo???i t???nh, th??nh ph??? kh??ng ???????c ????? tr???ng',
                'max:45' => 'Lo???i t???nh, th??nh ph??? kh??ng qu?? 45 k?? t???',
            ],
        ]);

        $result = Province::insert([
            'name' => request()->name,
            'type' => request()->type,
        ]);

        if($result) {
            return response()->success(1, 'Th??m t???nh, th??nh ph??? th??nh c??ng!');
        }

        return response()->error(2, 'Th??m t???nh, th??nh ph??? th???t b???i!');
    }

    public function update_tinh() {
        
        validator()->validate([
            'id' => [
                'required' => 'M?? t???nh, th??nh ph??? kh??ng ???????c ????? tr???ng',
                'exists:province' => 'M?? t???nh, th??nh ph??? kh??ng t???n t???i',
            ],
            'name' => [
                'required' => 'T??n t???nh, th??nh ph??? kh??ng ???????c ????? tr???ng',
                'max:45' => 'T??n t???nh, th??nh ph??? kh??ng qu?? 45 k?? t???',
            ],
            'type' => [
                'required' => 'Lo???i t???nh, th??nh ph??? kh??ng ???????c ????? tr???ng',
                'max:45' => 'Lo???i t???nh, th??nh ph??? kh??ng qu?? 45 k?? t???',
            ],
        ]);

        $result = Province::withTrashed()->find(request()->id)
        ->update([
            'name' => request()->name,
            'type' => request()->type,
        ]);

        if($result) {
            return response()->success(1, 'S???a t???nh, th??nh ph??? th??nh c??ng!');
        }

        return response()->error(2, 'S???a t???nh, th??nh ph??? th???t b???i!');
    }

    public function change_tinh($type) {
        
        validator()->validate([
            'id' => [
                'required' => 'M?? t???nh, th??nh ph??? kh??ng ???????c ????? tr???ng',
                'exists:province' => 'M?? t???nh, th??nh ph??? kh??ng t???n t???i',
            ],
        ]);

        if($type == 'hide') {
            $model = Province::withTrashed()->find(request()->id)->hide();
        } else {
            $model = Province::withTrashed()->find(request()->id)->show();
        }

        return response()->success(1, 'Thao t??c th??nh c??ng!');
    }

    public function delete_tinh() {
        return $this->change_tinh('hide');
    }

    public function undelete_tinh() {
        return $this->change_tinh('show');
    }

    public function create_huyen() {
        
        validator()->validate([
            'name' => [
                'required' => 'T??n qu???n, huy???n kh??ng ???????c ????? tr???ng',
                'max:45' => 'T??n qu???n, huy???n kh??ng qu?? 45 k?? t???',
                'unique:district' => 'T??n qu???n, huy???n ???? t???n t???i',
            ],
            'type' => [
                'required' => 'Lo???i qu???n, huy???n kh??ng ???????c ????? tr???ng',
                'max:45' => 'Lo???i qu???n, huy???n kh??ng qu?? 45 k?? t???',
            ],
            'province_id' => [
                'required' => 'M?? t???nh, th??nh ph??? kh??ng ???????c ????? tr???ng',
                'exists:province' => 'M?? t???nh, th??nh ph??? kh??ng t???n t???i',
            ],
        ]);

        $result = District::insert([
            'name' => request()->name,
            'type' => request()->type,
            'province_id' => request()->province_id,
        ]);

        if($result) {
            return response()->success(1, 'Th??m qu???n, huy???n th??nh c??ng!');
        }

        return response()->error(2, 'Th??m qu???n, huy???n th???t b???i!');
    }

    public function update_huyen() {
        
        validator()->validate([
            'id' => [
                'required' => 'M?? qu???n, huy???n kh??ng ???????c ????? tr???ng',
                'exists:district' => 'M?? qu???n, huy???n kh??ng t???n t???i',
            ],
            'name' => [
                'required' => 'T??n qu???n, huy???n kh??ng ???????c ????? tr???ng',
                'max:45' => 'T??n qu???n, huy???n kh??ng qu?? 45 k?? t???',
            ],
            'type' => [
                'required' => 'Lo???i qu???n, huy???n kh??ng ???????c ????? tr???ng',
                'max:45' => 'Lo???i qu???n, huy???n kh??ng qu?? 45 k?? t???',
            ],
            'province_id' => [
                'required' => 'M?? t???nh, th??nh ph??? kh??ng ???????c ????? tr???ng',
                'exists:province' => 'M?? t???nh, th??nh ph??? kh??ng t???n t???i',
            ],
        ]);

        $result = District::withTrashed()->find(request()->id)
        ->update([
            'name' => request()->name,
            'type' => request()->type,
            'province_id' => request()->province_id,
        ]);

        if($result) {
            return response()->success(1, 'S???a qu???n, huy???n th??nh c??ng!');
        }

        return response()->error(2, 'S???a qu???n, huy???n th???t b???i!');
    }

    public function change_huyen($type) {
        
        validator()->validate([
            'id' => [
                'required' => 'M?? qu???n, huy???n kh??ng ???????c ????? tr???ng',
                'exists:district' => 'M?? qu???n, huy???n kh??ng t???n t???i',
            ],
        ]);

        if($type == 'hide') {
            $model = District::withTrashed()->find(request()->id)->hide();
        } else {
            $model = District::withTrashed()->find(request()->id)->show();
        }

        return response()->success(1, 'Thao t??c th??nh c??ng!');
    }

    public function delete_huyen() {
        return $this->change_huyen('hide');
    }

    public function undelete_huyen() {
        return $this->change_huyen('show');
    }

    public function create_xa() {
        
        validator()->validate([
            'name' => [
                'required' => 'T??n x??, ph?????ng kh??ng ???????c ????? tr???ng',
                'max:45' => 'T??n x??, ph?????ng kh??ng qu?? 45 k?? t???',
                'unique:ward' => 'T??n x??, ph?????ng ???? t???n t???i',
            ],
            'type' => [
                'required' => 'Lo???i x??, ph?????ng kh??ng ???????c ????? tr???ng',
                'max:45' => 'Lo???i x??, ph?????ng kh??ng qu?? 45 k?? t???',
            ],
            'district_id' => [
                'required' => 'M?? qu???n, huy???n kh??ng ???????c ????? tr???ng',
                'exists:district' => 'M?? qu???n, huy???n kh??ng t???n t???i',
            ],
        ]);

        $result = Ward::insert([
            'name' => request()->name,
            'type' => request()->type,
            'district_id' => request()->district_id,
        ]);

        if($result) {
            return response()->success(1, 'Th??m x??, ph?????ng th??nh c??ng!');
        }

        return response()->error(2, 'Th??m x??, ph?????ng th???t b???i!');
    }

    public function update_xa() {
        
        validator()->validate([
            'id' => [
                'required' => 'M?? x??, ph?????ng kh??ng ???????c ????? tr???ng',
                'exists:ward' => 'M?? x??, ph?????ng kh??ng t???n t???i',
            ],
            'name' => [
                'required' => 'T??n x??, ph?????ng kh??ng ???????c ????? tr???ng',
                'max:45' => 'T??n x??, ph?????ng kh??ng qu?? 45 k?? t???',
            ],
            'type' => [
                'required' => 'Lo???i x??, ph?????ng kh??ng ???????c ????? tr???ng',
                'max:45' => 'Lo???i x??, ph?????ng kh??ng qu?? 45 k?? t???',
            ],
            'district_id' => [
                'required' => 'M?? qu???n, huy???n kh??ng ???????c ????? tr???ng',
                'exists:district' => 'M?? qu???n, huy???n kh??ng t???n t???i',
            ],
        ]);

        $result = Ward::withTrashed()->find(request()->id)
        ->update([
            'name' => request()->name,
            'type' => request()->type,
            'district_id' => request()->district_id,
        ]);

        if($result) {
            return response()->success(1, 'S???a x??, ph?????ng th??nh c??ng!');
        }

        return response()->error(2, 'S???a x??, ph?????ng th???t b???i!');
    }

    public function change_xa($type) {
        
        validator()->validate([
            'id' => [
                'required' => 'M?? x??, ph?????ng kh??ng ???????c ????? tr???ng',
                'exists:ward' => 'M?? x??, ph?????ng kh??ng t???n t???i',
            ],
        ]);

        if($type == 'hide') {
            $model = Ward::withTrashed()->find(request()->id)->hide();
        } else {
            $model = Ward::withTrashed()->find(request()->id)->show();
        }

        return response()->success(1, 'Thao t??c th??nh c??ng!');
    }

    public function delete_xa() {
        return $this->change_xa('hide');
    }

    public function undelete_xa() {
        return $this->change_xa('show');
    }
}