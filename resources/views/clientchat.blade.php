@extends('layouts.app')
<script src="{{ asset('js/app.js') }}"></script>

@section('content')
<div class="container">
<!-- <div id="justchat"> -->
    <div class="row justify-content-center"> 
               <div class="col-md-8">

            <div class="card">
                <div class="card-header" style="color:white;background-color:black"><b>Live Chat</b></div>

                <div class="card-body" id="cardbody" style="height: 600px;overflow-y: scroll;">
                    <div v-for="msg in messagez">
                        <div class="row" v-if="msg.receipent == 'client'">
                            <button class="btn btn-lg btn-primary" style="max-width: 300px;background-color: #0321c9;" disabled>
                            @{{ msg.message }}
                            </button>
                        </div>
                        <div class="row" v-else-if="msg.receipent == 'clientFromBot'">
                            <div>
                                <button class="btn btn-lg btn-primary" style="max-width: 300px;background-color: #0321c9;" disabled>
                                @{{ msg.message }}
                                </button> 
                            </div>
                            <br>
                            <div>                       
                                <button id="1" class="btn btn-sm btn-success" style="max-width: 300px;" @click="giveStar(1)">
                                1
                                </button>
                                <button id="2" class="btn btn-sm btn-success" style="max-width: 300px;" @click="giveStar(2)">
                                2
                                </button>
                                <button id="3" class="btn btn-sm btn-success" style="max-width: 300px;" @click="giveStar(3)">
                                3
                                </button>
                                <button id="4" class="btn btn-sm btn-success" style="max-width: 300px;" @click="giveStar(4)">
                                4
                                </button>
                                <button id="5" class="btn btn-sm btn-success" style="max-width: 300px;" @click="giveStar(5)">
                                5
                                </button>
                            </div>
                            
                        </div>
                        <div class="row" v-else-if="msg.receipent == 'clientRejected'">
                            <button class="btn btn-lg btn-primary" style="max-width: 300px;background-color: #189fac; border-color: #189fac;" disabled>
                            @{{ msg.message }}
                            </button>
                        </div>   
                        <div class="row" v-else-if="msg.receipent == 'lastreview'">
                            <div>
                                <button class="btn btn-lg btn-primary" style="max-width: 300px;background-color: #0321c9;" disabled>
                                @{{ msg.message }}
                                </button> 
                            </div>
                            <br>
                            <div v-if="english == 0">                       
                                <button id="1" class="btn btn-sm btn-success" style="max-width: 300px;" @click="giveFbk('Diselesaikan')">
                                Diselesaikan
                                </button>
                                <button id="2" class="btn btn-sm btn-success" style="max-width: 300px;" @click="giveFbk('Separa Diselesaikan')">
                                Separa Diselesaikan
                                </button>
                                <button id="3" class="btn btn-sm btn-success" style="max-width: 300px;" @click="giveFbk('Tidak Diselesaikan')">
                                Tidak Diselesaikan
                                </button>                        
                            </div>
                            <div v-if="english == 1">                       
                                <button id="4" class="btn btn-sm btn-success" style="max-width: 300px;" @click="giveFbk('Resolved')">
                                Resolved
                                </button>
                                <button id="5" class="btn btn-sm btn-success" style="max-width: 300px;" @click="giveFbk('Partially Resolved')">
                                Partially Resolved
                                </button>
                                <button id="6" class="btn btn-sm btn-success" style="max-width: 300px;" @click="giveFbk('Not Resolved')">
                                Not Resolved
                                </button>                        
                            </div>                             
                        </div>   
                        <div class="row justify-content-end" v-else-if="msg.receipent != 'client'">
                            <button class="btn btn-lg btn-primary" style="max-width: 300px;background-color: #189fac; border-color: #189fac;" disabled>
                            @{{ msg.message }}
                            </button>
                        </div>
                      
                        <br />
                    </div>       
                </div>
                <div class="card-footer px-0 py-0">
                    <input                    
                    class="form-control"
                    id="msgArea"
                    @keyup.enter="sendMessage($event)"
                    placeholder="Enter Your Message Here"
                    type="text"
                    style="padding: 25px;"
                />
                </div>
            </div>
        </div>
    </div>
<!-- </div> -->
</div>

<script>
const queryString = window.location.search;
const urlParams = new URLSearchParams(queryString);
const id = urlParams.get('id');
//(keyup.enter) = "sendMessage(msgInp.value)"

new Vue({
  el: '#app',
  data: {      
          messagez: [],
          agentid: '',
          star: '',
          fbk: '',
          first: 0,
          english: 0,
          dofbk: 0,
          doneRead: 1,
          intervalid: 0,        
      },
    mounted(){
        var vm = this;
        document.getElementById("msgArea").disabled = true;
        vm.intervalid = setInterval(() => {
            this.getMessages();
        }, 3000);        
    },
    methods:{
        sendMessage(e){
            if (!!e.target.value.trim()){
            
            var vm = this;
            var message = e.target.value;
            document.getElementById("msgArea").value="";
            vm.messagez.push({'message': message});

            setTimeout(() => {
                this.scrollBottom();
            }, 200); 


            if (vm.agentid == ""){
                //console.log("testsets");
            }else{
                if (vm.star == ""){
                     axios.post('/public/sendmsg',{'id': id,'message': message,'agentId': vm.agentid}).then(function (response) {
                    
                    });
                 }
                //else{
                //     if (vm.fbk == ""){
                //         vm.giveFbk(message);
                //     }
                // }               
            }
          }
        },
        scrollBottom(){
            var objDiv = document.getElementById("cardbody");
            objDiv.scrollTop = objDiv.scrollHeight;
        },  
        giveStar(star){
            var vm = this;
            if (vm.star == ""){
                for (let index = 1; index < 6; index++) {
                    if (star != index){
                        document.getElementById(index).style.display = "none";
                    }                  
                }
                 vm.star = star;
                //document.getElementById("msgArea").disabled = false;
                vm.constructFbk();      
            }                 
        },
        giveFbk(fbk){
            var vm = this;
            if (vm.fbk == ""){

            vm.fbk = fbk;
            
            axios.post('/public/giveFeedback',{'userId': id,'fbk': vm.fbk,'star': vm.star})
              .then(function (response) {
                  //console.log(response);
                    if (response.data[1].stat == "ok"){
                        vm.messagez.push({"message": response.data[0].message,"receipent": response.data[2].receipent});
                        clearInterval(vm.intervalid);
                        // setTimeout(() => {
                        //     document.getElementById("msgArea").disabled = true;
                        // },100)                        
                    }
              });
            }
        },
        constructFbk(){
            var vm = this;
            vm.dofbk = 1;
            vm.english == 1 ? vm.messagez.push({"receipent": 'lastreview',"message": 'Please provide feedback on our service'}) : vm.messagez.push({"receipent": 'lastreview',"message": 'Sila bagi maklum balas terhadap perkhidmatan kami'});
        },
        getMessages(){
            var vm = this;
            
            if (vm.doneRead == 1){
                vm.doneRead = 0;
            axios.get('/public/getclientmsg?id=' + id)
              .then(function (response) {
                //console.log(response);
                // handle success
                if (response.data != "nothing"){
                        if (vm.first != 1){
                            if (response.data.message.includes("Please wait for our agent to join") || response.data.message.includes("unable to serve you")){
                                vm.english = 1;
                            }
                        }
                   
                       vm.first = 1;

                        vm.agentid = response.data.agentid;                   
                      if (vm.agentid != "" && vm.agentid != 0){
                            if (document.getElementById("msgArea").disabled ===  true){     
                                document.getElementById("msgArea").disabled = false;
                            }
                      }                 
                               
                    vm.messagez.push(response.data);
                    vm.doneRead = 1;
                    setTimeout(() => {
                        vm.scrollBottom();   
                        //console.log(response.data);
                        if (response.data.receipent == "clientFromBot" || response.data.receipent == "clientRejected"){                                          
                            document.getElementById("msgArea").disabled = true;
                        } 
                    }, 200);    
                                                     
                }else{
                    if (vm.first==0){
                        clearInterval(vm.intervalid);

                    } 
                    vm.doneRead = 1;
                }
              })
            }
        },
    }
})
Vue.config.silent = true
</script>
@endsection
