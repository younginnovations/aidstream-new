@extends('app')

@section('title', trans('title.participating_organisation'). ' - ' . $activityData->IdentifierTitle)

@section('content')
    {{Session::get('message')}}
    <div class="container main-container">
        <div class="row">
            @include('includes.side_bar_menu')
            <div class="col-xs-9 col-md-9 col-lg-9 content-wrapper">
                @include('includes.response')
                <div class="element-panel-heading">
                    <div>
                        <span>@lang('element.add_participating_organisation')</span>
                        <div class="element-panel-heading-info"><span>{{$activityData->IdentifierTitle}}</span></div>
                    </div>
                    <div class="panel-action-btn">
                        <a href="{{ route('activity.show', $id) }}" class="btn btn-primary btn-view-it">@lang('global.view_activity')
                        </a>
                    </div>
                </div>
                <div class="col-xs-12 col-md-8 col-lg-8 element-content-wrapper">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="create-form">
                                <div v-cloak class="create-form" id="participatingContainer" data-organization="{{json_encode($participatingOrganizations)}}"
                                     data-partnerOrganization="{{json_encode($partnerOrganizations)}}" data-activityId="{{$id}}"
                                     data-organizationRoles="{{json_encode($organizationRoles)}}">
                                    <div v-if="display_server_error_message" class="alert alert-danger">@{{ server_error_message }}</div>
                                    {{Form::open()}}
                                    <participating-org v-for="(organisation,index) in organisations" v-on:remove="removeOrganisation(index)"
                                                       v-on:search="setCurrentOrganization(index,$event)"
                                                       :organisation="organisation" :index="index"
                                                       v-on:display="displayModal($event)"
                                                       :display_error="display_error"
                                                       :organisation_roles="organisationRoles"
                                                       :partner_organisations="partnerOrganisations">
                                    </participating-org>
                                    <button class="addMore" type="button" @click="addOrganisations()">Add another organisation</button>
                                    <modal v-show="showModal" v-on:close="closeModal" :organisation="currentOrganisation"
                                           :registrar_list="registrarList"></modal>
                                    <button class="btn btn-submit btn-form" type="button" v-on:click.prevent="onSubmit">Save</button>
                                    <a class="btn btn-cancel" href="{{route('activity.show', $id)}}">Cancel</a>
                                </div>
                                {{Form::close()}}
                            </div>
                        </div>
                    </div>
                </div>
                @include('includes.activity.element_menu')
            </div>
        </div>
    </div>

    <div id="participating-form" class="hidden">
        <div class="collection_form has_add_more">
            <div class="form-group">
                <div class="organisation-role" v-bind:class="{'has-error': (organisation.organization_role == '' && display_error)}">
                    {{Form::label('Organisation Role',trans('elementForm.organisation_role'),['class' => '.control-label'])}}
                    <ul>
                        <li v-for="(role,code) in organisation_roles" v-bind:class="{'active': (organisation.organization_role == code)}">
                            <label>
                                <input type="radio" :checked="organisation.organization_role == code" :name="index" v-bind:value='code' v-on:change='onchange($event)'>@{{ code }} - @{{ role }}
                            </label>
                        </li>
                    </ul>
                    <div v-if="(organisation.organization_role == '' && display_error)" class="text-danger">Organisation Role is required.</div>
                </div>

                <div class="form-group" v-bind:class="{'has-error': (organisation.organization_type == '' && display_error)}">
                    {{Form::label('organisation_Type',trans('elementForm.organisation_type'),['class' => 'control-label'])}}
                    {{Form::select('organization_type',$organizationTypes, null,['class' => 'form-control ignore_change', 'v-bind:value' => 'organisation.organization_type', 'v-on:change'=>'onchange($event)', 'placeholder' => 'Please select the following options.','v-bind:readonly' => "disable_options[index]"])}}
                    <div v-if="(organisation.organization_type == '' && display_error)" class="text-danger">Organisation Type is required.</div>
                </div>

                <div class="form-group" v-bind:class="{'has-error': (organisation.country == '' && display_error)}">
                    {{Form::label('country','Country the organization is based in',['class' => 'control-label'])}}
                    {{Form::select('country',$countries, null,['class' => 'form-control ignore_change', 'v-bind:value' => 'organisation.country', 'v-on:change'=>'onchange($event)', 'placeholder' => 'Please select the following options.', 'v-bind:readonly' => "disable_options[index]"])}}
                    <div v-if="(organisation.country == '' && display_error)" class="text-danger">Country is required.</div>
                </div>
                <div class="form-group" v-bind:class="{'has-error': (organisation.narrative[0]['narrative'] == '' && display_error) }">
                    {{Form::label('Organization',trans('elementForm.organisation'),['class' => 'control-label'])}}
                    {{Form::text('organization',null,['class' => 'form-control ignore_change','v-bind:value' => "organisation.narrative[0]['narrative']",'@focus' => 'displaySuggestion($event)', '@keydown.tab'=> 'hideSuggestion','@blur'=>'hide($event)','autocomplete' => 'off', 'readonly' => true])}}

                    <div v-if="(organisation.narrative[0]['narrative'] == '' && display_error)" class="text-danger">Organisation Name is required.</div>


                    <div v-if="display_org_list" class="publisher-wrap">
                        <ul class="filter-publishers">
                            <li>
                                <div class="search-publishers">
                                    <input type="search" :value="keywords[index]" placeholder="Filter by organisation name..." @keyup ='search($event)'>
                                </div>
                            </li>
                        </ul>

                        <ul v-if="suggestions.length > 0" class="found-publishers filter-publishers scroll-list">
                            <li><p>Choose an organisation from below</p></li>
                            <li v-for="(publisher, index) in suggestions">
                                <a href="#" v-on:click="selected($event)" v-bind:selectedSuggestion="index">
                                    <strong v-bind:selectedSuggestion="index">@{{publisher.identifier}} @{{publisher.name}}</strong>
                                    <div class="partners">
                                        <div class="pull-left">
                                            <span v-bind:selectedSuggestion="index">Type: @{{publisher.type}}</span>
                                        </div>
                                        <div class="pull-right">
                                            <span class="suggest-edit">Suggest Edit</span>
                                        </div>
                                    </div>
                                </a>

                            </li>
                            <li><p>The above list is pulled from IATI Registry publisher's list.</p></li>
                        </ul>

                        <ul v-if="display_partner_org && (partner_organisations.length > 0)" class="found-publishers">
                            <li class="publishers-list scroll-list">
                                <p>From your Partner Organization List</p>
                                <div v-for="(partnerOrganization, index) in partner_organisations">
                                    <a href="#" v-on:click="partnerSelected($event)" v-bind:selectedPartner="index">
                                        <strong v-bind:selectedPartner="index">@{{ partnerOrganization.name ? partnerOrganization.name[0]['narrative'] : 'No name'}}</strong>
                                        <span class="language">en</span>
                                    </a>
                                    <div class="partners">
                                        <div class="pull-left">
                                            <span v-bind:selectedPartner="index" class="tick">@{{ partnerOrganization.identifier }} </span>
                                        </div>
                                        <div class="pull-right">
                                            <span class="suggest-edit" v-if="partnerOrganization.is_publisher">Suggest Edit</span>
                                            <span class="edit-activity" v-if="!partnerOrganization.is_publisher">Edit</span>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li><p>The above list is pulled from your organisation data.</p></li>
                        </ul>

                        <ul v-if="display_org_finder" class="not-found-publisher">
                            <li><p>It seems there's no matching organisation in IATI Registry of publishers. You may do one of the following at this point.</p></li>
                            <li class="contact-org" id="orgFinder">
                                <a href="#">
                                    <p class="contact-heading">Contact Organisation</p>
                                    <p>Send them a message letting them know about this.</p>
                                </a>
                            </li>
                            <li class="or">Or</li>
                            <li id="orgFinder">
                                <a href="#" @click="display()">
                                <h3 class="contact-heading">Use Organization Finder <span> (org-id.guide)</span></h3>
                                <p>Use our organization finder helper to get a new identifier for this.</p>
                                <p><span class="caution">Caution:</span> Please beware that this can be a long and
                                    tedious process. It may be the case that you will not
                                    find the organization even with this. In this case, leave the identifier field blank
                                    and just mention organisation name only.</p>
                                </a>
                            </li>
                        </ul>
                    </div>

                </div>
                <div class="form-group organisation-identifier">
                    {{Form::label('organisation_identifier','Organisation Identifier:',['class' => 'control-label'])}}
                    @{{organisation.identifier}}
                </div>
                <div class="form-group">
                    {{Form::label('activity_id',trans('elementForm.activity_id'),['class' => 'control-label'])}}
                    {{Form::text('activity_id',null,['class' => 'form-control','v-bind:value' => 'organisation.activity_id' ])}}
                </div>
                <button class="remove_organisation" type="button" @click="remove()" v-bind:index="index">Remove Organisation</button>
            </div>
        </div>
    </div>

    <div class="hidden" id="modalComponent">
        <div class="modal fade org-modal" id="myModal" role="dialog">
            <div class="modal-dialog ">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" @click="close">&times;</button>
                        <h4 class="modal-title">Organization Finder</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            {{Form::label('Organisation Type',trans('elementForm.organisation_type'),['class' => 'control-label'])}}
                            {{Form::select('organization_type',$organizationTypes, null,['class' => 'form-control ignore_change', 'v-bind:value' => 'organisation.organization_type', 'placeholder' => 'Please select the following options.', 'v-on:change' => 'getRegistrars($event)'])}}
                        </div>

                        <div class="form-group">
                            {{Form::label('country','Country the organization is based in',['class' => 'control-label'])}}
                            {{Form::select('country',$countries, null,['class' => 'form-control ignore_change', 'v-bind:value' => 'organisation.country', 'placeholder' => 'Please select the following options.','v-on:change' => 'getRegistrars($event)'])}}
                        </div>
                        <div class="suggestions" v-if="display_registrar_list">
                            <p>PLEASE CHOOSE A LIST FROM BELOW:</p>
                            <div class="lists scroll-list">
                                <ul>
                                    <li v-for="(list,index) in registrar_list[0]">
                                        <div class="register-list">
                                            <label>
                                                <input type="radio" name="registrar" v-on:change="displayForm($event)"
                                                       v-bind:value="list['code']"/>
                                                <span>@{{ list['name']['en'] }}
                                                    <strong>(@{{ list['code'] }})</strong></span>
                                            </label>
                                        </div>
                                        <div class="score-block"><span>Quality Score: <strong>@{{ list['quality'] }}</strong></span><span><a
                                                        v-bind:href="list.url" target="_blank">View this list →</a></span>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div v-if="display_org_info_form">
                                <div class="form-group">
                                    {{Form::label('Organisation Name',trans('elementForm.organisation_name'),['class' => 'control-label'])}}
                                    {{Form::text('name', null,['class' => 'form-control ignore_change', "v-bind:value" => "organisation.narrative[0]['narrative']", "@blur" => 'updateOrgName($event)'])}}
                                </div>

                                <div class="form-group">
                                    {{Form::label('Identifier','Organisation Registration Number',['class' => 'control-label'])}}
                                    {{Form::text('identifier', null,['class' => 'form-control ignore_change', 'v-bind:value' => 'organisation.identifier', "@blur" => 'updateOrgIdentifier($event)'])}}
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-form" type="button" @click="close">Use this organisation</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="reset-form-option">
                            <a @click="resetForm">Reset form</a>
                            <p>Reset the above form to start again.</p>
                        </div>
                        {{--<button type="button" class="btn btn-default" data-dismiss="modal" @click="close">Close</button>--}}
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://unpkg.com/vue"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script>
      Vue.component('participating-org', {
        template: '#participating-form',
        data: function () {
          return {
            display_org_list: false,
            display_org_finder: false,
            display_partner_org: false,
            suggestions: [],
            searching: false,
            disable_options: [],
            keywords: []
          }
        },
        updated: function () {
          $('.scroll-list').jScrollPane({autoReinitialise: true});
        },
        created: function () {
          this.keywords[this.index] = '';
          this.disable_options[this.index] = (this.organisation.is_publisher) ? true : false;
        },
        props: ['partner_organisations', 'organisation', 'index', 'display_error', 'organisation_roles'],
        methods: {
          displaySuggestion: function () {
            this.keywords[this.index] = '';
            this.display_org_list = true;
            this.display_partner_org = true;
          },
          search: function (event) {
            var self = this;
            self.display_partner_org = true;
            this.keywords[this.index] = event.target.value;
            if (event.target.value.trim().length > 3) {
              self.display_partner_org = false;
              if (!self.searching) {
                self.searching = true;
                self.disable_options[this.index] = false;
                this.suggestions.splice(0, this.suggestions.length);
                setTimeout(function () {
                  axios.get('/findpublisher?name=' + event.target.value + '&identifier=' + event.target.value)
                    .then(function (response) {
                      self.searching = false;
                      response.data.forEach(function (publisher) {
                        publisher.is_publisher = true;
                        self.suggestions.push(publisher);
                      });
                    })
                    .catch(function (error) {
                      self.suggestions.splice(0, self.suggestions.length);
                      self.display_org_finder = true;
                      self.searching = false;
                    });
                }, 1000);
              }
            } else {
              this.suggestions.splice(0, this.suggestions.length);
              this.display_org_finder = false;
              this.display_partner_org = true;
            }
            this.$emit('search', this.index);
          },
          hideSuggestion: function () {
            this.display_org_list = false;
            this.display_partner_org = false;
            this.display_org_finder = false;
            this.suggestions.splice(0, this.suggestions.length);
          },
          remove: function () {
            this.disable_options.splice(this.index, 1);
            this.$emit('remove', this.index);
          },
          onchange: function (event) {
            if (event.target.name == this.index) {
              this.organisation['organization_role'] = event.target.value;
            } else {
              this.organisation[event.target.name] = event.target.value;
            }
          },
          display: function () {
            this.display_org_list = false;
            this.display_org_finder = false;
            var country = this.organisation['country'];
            var self = this;
            if (country != "") {
              axios.get('/findorg?country=' + country)
                .then(function (response) {
                  self.$emit('display', response.data);
                });
            }
            self.$emit('display', []);
          },
          selected: function (event) {
            this.disable_options[this.index] = true;
            this.display_org_list = false;
            var selectedIndex = event.target.getAttribute('selectedSuggestion');
            this.organisation['organization_type'] = this.suggestions[selectedIndex]['type'];
            this.organisation['is_publisher'] = this.suggestions[selectedIndex]['is_publisher'];
            this.organisation['identifier'] = this.suggestions[selectedIndex]['identifier'];
            this.organisation['country'] = this.suggestions[selectedIndex]['country'];
            this.organisation['narrative'][0]['narrative'] = this.suggestions[selectedIndex]['name'];
            this.organisation['narrative'][0]['language'] = 'en';
            this.suggestions.splice(0, this.suggestions.length);
          },
          hide: function (event) {
            if (!event.relatedTarget) {
              this.display_org_list = false;
              this.display_org_finder = false;
              this.display_partner_org = false;
              this.suggestions.splice(0, this.suggestions.length);
            }
          },
          partnerSelected: function (event) {
            var selectedIndex = event.target.getAttribute('selectedPartner');

            this.organisation['organization_type'] = this.partner_organisations[selectedIndex]['type'];
            this.organisation['is_publisher'] = this.partner_organisations[selectedIndex]['is_publisher'];
            this.organisation['identifier'] = this.partner_organisations[selectedIndex]['identifier'];
            this.organisation['country'] = this.partner_organisations[selectedIndex]['country'];
            this.organisation['org_data_id'] = this.partner_organisations[selectedIndex]['id'];
            this.organisation['narrative'][0]['narrative'] = this.partner_organisations[selectedIndex]['name'][0]['narrative'];
            this.organisation['narrative'][0]['language'] = 'en';
            this.disable_options[this.index] = (this.organisation.is_publisher) ? true : false;
            this.display_partner_org = false;
            this.display_org_list = false;
          }
        }
      });

      Vue.component('modal', {
        template: '#modalComponent',
        props: ['organisation', 'registrar_list'],
        data: function () {
          return {
            display_org_info_form: false,
            selectedRegistrar: '',
            display_registrar_list: false
          }
        },
        beforeUpdate: function () {
          if (this.registrar_list[0] != undefined) {
            if (this.registrar_list[0].length != 0) {
              this.display_registrar_list = true;
            }
          }
        },
        updated: function () {
          $('.scroll-list').jScrollPane({autoReinitialise: true});
        },
        methods: {
          close: function () {
            this.display_org_info_form = false;
            this.$emit('close', false);
          },
          displayForm: function (event) {
            this.selectedRegistrar = event.target.getAttribute('value');
            this.display_org_info_form = true;
          },
          getRegistrars: function (event) {
            var self = this;
            this.organisation[event.target.name] = event.target.value;
            axios.get('/findorg?country=' + event.target.value)
              .then(function (response) {
                self.registrar_list.splice(0, self.registrar_list.length);
                self.registrar_list.push(response.data);
              });
          },
          updateOrgName: function (event) {
            this.organisation['narrative'][0]['narrative'] = event.target.value;
          },
          updateOrgIdentifier: function ($event) {
            this.organisation['identifier'] = '';
            this.organisation['identifier'] = this.selectedRegistrar + '-' + event.target.value;
          },
          resetForm: function () {
            this.defaultData();
          },
          defaultData: function () {
            this.organisation['organization_type'] = '';
            this.organisation['organization_role'] = '';
            this.organisation['is_publisher'] = '';
            this.organisation['identifier'] = '';
            this.organisation['narrative'][0]['narrative'] = '';
            this.organisation['country'] = '';
            this.organisation['narrative'][0]['language'] = '';
          }
        }
      });

      new Vue({
        el: '#participatingContainer',
        data: {
          organisations: [],
          partnerOrganisations: [],
          showModal: false,
          currentOrganisation: [],
          registrarList: [],
          display_error: false,
          display_server_error_message: false,
          server_error_message: '',
          organisationRoles: []
        },
        mounted: function () {
          if (JSON.parse(this.$el.getAttribute('data-organization'))) {
            this.organisations = JSON.parse(this.$el.getAttribute('data-organization'));
          } else {
            this.organisations.push({
              "identifier": "",
              "activity_id": "",
              "organization_role": "",
              "organization_type": "",
              "country": "",
              "org_data_id": "",
              "narrative": [{"narrative": "", "language": ""}]
            });
          }

          if (JSON.parse(this.$el.getAttribute('data-organizationRoles'))) {
            this.organisationRoles = JSON.parse(this.$el.getAttribute('data-organizationRoles'));
          }

          if (JSON.parse(this.$el.getAttribute('data-partnerOrganization'))) {
            this.partnerOrganisations = JSON.parse(this.$el.getAttribute('data-partnerOrganization'));
          }
        },
        methods: {
          setCurrentOrganization: function (index) {
            this.currentOrganisation = this.organisations[index];
          },
          displayModal: function (event) {
            this.registrarList.splice(0, this.registrarList.length);
            if (event) {
              this.registrarList.push(event);
            }
            this.showModal = true;
            $('#myModal').modal();
          },
          addOrganisations: function () {
            this.display_error = false;
            this.organisations.push({
              "identifier": "",
              "activity_id": "",
              "organization_role": "",
              "organization_type": "",
              "country": "",
              "org_data_id": "",
              "narrative": [{"narrative": "", "language": ""}]
            });
          },
          removeOrganisation: function (index) {
            if (this.organisations.length > 1) {
              this.organisations.splice(index, 1);
            }
          },
          closeModal: function () {
            this.showModal = false;
            $('.modal-backdrop').remove();
          },
          onSubmit: function () {
            var activityId = this.$el.getAttribute('data-activityId');
            var route = '/activity/' + activityId + '/participating-organization/0';
            var self = this;
            if (this.isValid()) {
              axios.put(route, {participating_organization: self.organisations})
                .then(function (response) {
                  window.location.href = '/activity/' + activityId;
                }).catch(function (error) {
                self.server_error_message = error.response.data;
                self.display_server_error_message = true;
              });
            }
          },
          isValid: function () {
            var self = this;
            var status = true;
            this.organisations.forEach(function (organisation, index) {
              if (organisation.country === '' || organisation.narrative[0]['narrative'] === '' || organisation.organization_type === '' || organisation.organization_role === '') {
                self.display_error = true;
                status = false;

                return false;
              }
            });
            return status;
          }
        }
      });
    </script>
@endsection
