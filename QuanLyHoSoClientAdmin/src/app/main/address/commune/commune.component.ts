import {Component, Injector, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, Validators} from "@angular/forms";
import {AddressService} from "../../../services/address.service";
import {MessageService} from "primeng/api";
import {ActivatedRoute, Router} from "@angular/router";
import {ScriptService} from "../../../libs/script.service";
import {Location} from "@angular/common";
import { ShareService } from 'src/app/services/share.service';

declare var $:any;

@Component({
  selector: 'app-commune',
  templateUrl: './commune.component.html',
  styleUrls: ['./commune.component.css'],
  providers: [MessageService]
})
export class CommuneComponent extends ScriptService implements OnInit {
  totalRecords: number;
  first = 0;
  rows = 10;
  listCommune = [];
  submitted = false;
  aoe: boolean;
  form: FormGroup;

  constructor(
    injector: Injector,
    private addressService: AddressService,
    private formBuilder: FormBuilder,
    private messageService: MessageService,
    private router: Router,
    private route: ActivatedRoute,
    private location: Location,
    private shareService: ShareService
  ) {
    super(injector)
  }

  ngOnInit(): void {
    let elem = document.getElementsByClassName('script');
    if (elem.length != undefined) {
      for (let i = elem.length - 1; 0 <= i; i--) {
        elem[i].remove();
      }
    }
    this.loadScripts();

    this.loadData({first: this.first, rows: this.rows});

    this.form = this.formBuilder.group({
      id: [''],
      name: ['', [Validators.required, Validators.maxLength(45)]],
      type: ['', [Validators.required]]
    })
  }

  loadData(event) {
    this.first = event.first;
    this.rows = event.rows;
    let id = this.route.snapshot.params['id'];
    this.addressService.paginationCommune(id, this.first, this.rows).subscribe((res: any) => {
      this.listCommune = res.data;
      this.totalRecords = res.total;
    })
  }

  edit(id) {
    this.submitted = false;
    this.aoe = false;
    $("#myModal").modal("show");
    this.addressService.getCommuneById(id).subscribe((data: any) => {
      this.form.patchValue({
        id: data.id,
        name: data.name,
        type: data.type
      })
    })
  }

  delete(id) {
    if (confirm("B???n mu???n x??a x?? n??y?")) {
      this.shareService.openLoading();
      this.addressService.deleteCommune(id).subscribe((res: any) => {
        this.shareService.closeLoading();
        this.loadData({first: this.first, rows: this.rows});
        this.messageService.add({severity: 'success', summary: 'Th??nh c??ng!', detail: "Xo?? x?? th??nh c??ng!"});
      }, err => {
        this.shareService.closeLoading();
        this.messageService.add({severity: 'error', summary: 'Th???t b???i!', detail: err.error.message});
      })
    }
  }

  create() {
    this.submitted = false;
    this.form.reset();
    this.aoe = true;
    $("#myModal").modal("show");
  }

  onSubmit() {
    this.submitted = true;
    if (this.form.invalid) {
      return;
    }

    let commune = {
      name: this.form.value.name,
      type: this.form.value.type,
      district_id: this.route.snapshot.params['id']
    }
    if (this.aoe == true) {
      this.shareService.openLoading();
      this.addressService.createCommune(commune).subscribe((res: any) => {
        this.shareService.closeLoading();
        this.submitted = false;
        this.loadData({first: this.first, rows: this.rows});
        $("#myModal").modal("hide");
        this.messageService.add({severity: 'success', summary: 'Th??nh c??ng!', detail: "Th??m x?? th??nh c??ng!"});
      }, err => {
        this.shareService.closeLoading();
        this.messageService.add({severity: 'error', summary: 'Th???t b???i!', detail: err.error.message});
      })
    } else {
      this.shareService.openLoading();
      this.addressService.updateCommune(this.form.value.id, commune).subscribe((res: any) => {
        this.shareService.closeLoading();
        this.submitted = false;
        this.loadData({first: this.first, rows: this.rows});
        $("#myModal").modal("hide");
        this.messageService.add({severity: 'success', summary: 'Th??nh c??ng!', detail: "S???a x?? th??nh c??ng!"});
      }, err => {
        this.shareService.closeLoading();
        this.messageService.add({severity: 'error', summary: 'Th???t b???i!', detail: err.error.message});
      })
    }
  }

  redirectDistrict() {
    this.location.back();
  }

  status(event) {
    if (event.target.checked == true) {
      if (confirm("B???n mu???n hi???n x?? n??y?")) {
        this.shareService.openLoading();
        this.addressService.unDeleteCommune(event.target.value).subscribe((res:any) => {
          this.shareService.closeLoading();
          // this.loadData({first: this.first, rows: this.rows});
          this.messageService.add({severity: 'success', summary: 'Th??nh c??ng!', detail: "Hi???n th??? x?? th??nh c??ng!"});
        }, err => {
          this.shareService.closeLoading();
          this.loadData({first: this.first, rows: this.rows});
          this.messageService.add({severity: 'error', summary: 'Th???t b???i!', detail: err.error.message});
        })
      } else {
        this.loadData({first: this.first, rows: this.rows});
      }
    } else {
      if (confirm("B???n mu???n ???n x?? n??y?")) {
        this.shareService.openLoading();
        this.addressService.deleteCommune(event.target.value).subscribe((res:any) => {
          this.shareService.closeLoading();
          // this.loadData({first: this.first, rows: this.rows});
          this.messageService.add({severity: 'success', summary: 'Th??nh c??ng!', detail: "???n x?? th??nh c??ng!"});
        }, err => {
          this.shareService.closeLoading();
          this.loadData({first: this.first, rows: this.rows});
          this.messageService.add({severity: 'error', summary: 'Th???t b???i!', detail: err.error.message});
        })
      } else {
        this.loadData({first: this.first, rows: this.rows});
      }
    }
  }

}
