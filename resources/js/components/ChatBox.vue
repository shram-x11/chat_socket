<template>
    <div>
        <div>
            <p v-for="message in messages">
                <template v-if=" message.type == 'file'">
                    <a target="_blank" :href="'/storage/' + message.content"> Загружен файл {{message.content}}</a>
                </template>
                <template v-else>
                    {{ message.content }}
                </template>
            </p>
            <input v-model="text">
            <button @click="postMessage" :disabled="!contentExists">submit</button>
        </div>
        <div>
            <video class="img-responsive" autoplay id='video' ref="video">
                Your browser does not support the video tag.
            </video>
            <video class="img-responsive" autoplay id='localVideo'>
                Your browser does not support the video tag.
            </video>
        </div>
        <button class="btn btn-warning btn-sm pull-right" @click="startVideoCallToUser()" type="button">
            <span class="fa fa-video-camera"></span> Video Call
        </button>
        <button class="btn btn-warning btn-sm pull-right" @click="startAudioCallToUser()" type="button">
            <span class="fa fa-video-camera"></span> Audio Call
        </button>
        <button class="btn btn-warning btn-sm pull-right" @click="closeCall(2)" type="button">
            <span class="fa fa-video-camera"></span> End Call
        </button>
        <button class="btn btn-warning btn-sm pull-right" @click="answerCall()" :disabled="!incomingCall" type="button">
            <span class="fa fa-video-camera"></span> Answer Call
        </button>
        <div class="card card-default">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-9">
                        <input type="file" id="file" ref="file" v-on:change="onFileChange" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-success btn-block" @click="uploadFile">Upload File</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
	var seconds = 0;
	var myTimer;

	var pc;
	var offers;
	var first = true;
	var streams;
	var localStream;
	const offerOptions = {
		offerToReceiveAudio: 1,
		offerToReceiveVideo: 1
	};
	var connect = false;
	// pc.onaddstream = function (event) {
	// 	console.log('addstream')
	// 	document.getElementById("localVideo").srcObject = event.stream;
	// };

	function clock() {
		myTimer = setInterval(myClock, 1000);

		function myClock() {
			seconds = seconds + 1;
		}
	}

	export default {
		props: [
			'chat_id',
			'user_id',
		],
		data() {
			return {
				video: {},
				text: '',
				messages: [],
				file: '',
				seconds: 0,
				incomingCall: false,
				offer: '',
				initial: false,
				type: {video: true, audio: true},
			}
		},
		computed: {
			contentExists() {
				return this.text.length > 0;
			}
		},
		methods: {
			closeCall() {
				streams.getTracks().forEach(function (track) {
					track.stop();
				});
				// pc.close()
				axios.post('/chatSend/' + this.chat_id, {message: 'Звонок длительностью ' + seconds + 'с',}).then(({data}) => {
					this.messages.push(data);
					console.log('send close', data)
				})
				axios.post('/chatSend/' + this.chat_id, {video: '', action: 'close'}).then(({data}) => {
					document.location.reload(true);
				})
				clearInterval(myTimer);
				seconds = 0;


			},
			onFileChange() {
				this.file = this.$refs.file.files[0];
			},
			uploadFile() {
				let formData = new FormData();
				formData.append('file', this.file);
				axios.post('/chatSend/' + this.chat_id,
					formData,
					{
						headers: {
							'Content-Type': 'multipart/form-data'
						}
					}).then(({data}) => {
					console.log(this.messages);
					this.messages.push(data);
				})
			},
			postMessage() {
				axios.post('/chatSend/' + this.chat_id, {message: this.text,}).then(({data}) => {
					this.messages.push(data);
					console.log('send text', data)
					this.text = '';
				})
			},
			startVideoCallToUser() {
				this.type = {video: true, audio: true};
				this.initial = true;
				this.sendVideoRequest();
				// this.openCamera();
				// this.sendOffer();

			},
			answerCall() {
				this.sendAnswer(offers)
			},
			startAudioCallToUser() {
				this.type = {video: false, audio: true};
				this.initial = true;
				this.sendRequestOfferAudio();
			},
			sendVideoRequest() {
				// this.sendOffer();
				this.sendRequestOffer();

			},
			sendOffer() {
				var id = this.chat_id;
				pc.createOffer(offerOptions).then(function (offer) {
					return pc.setLocalDescription(offer);
				})
					.then(function () {
						axios.post('/chatSend/' + id, {
							video: JSON.stringify(pc.localDescription),
							action: 'offer',
						}).then(({data}) => {
							console.log('send offer')
						})
					})
			},
			sendRequestOffer() {
				var id = this.chat_id;
				pc.createOffer(offerOptions).then(function (offer) {
					return pc.setLocalDescription(offer);
				})
					.then(function () {
						axios.post('/chatSend/' + id, {
							video: JSON.stringify(pc.localDescription),
							action: 'offer_request',
						}).then(({data}) => {
							console.log('send request offer')
						})
					})
			},
			sendRequestOfferAudio() {
				var id = this.chat_id;
				pc.createOffer(offerOptions).then(function (offer) {
					return pc.setLocalDescription(offer);
				})
					.then(function () {
						axios.post('/chatSend/' + id, {
							video: JSON.stringify(pc.localDescription),
							action: 'offer_request_audio',
						}).then(({data}) => {
							console.log('send request offer')
						})
					})
			},
			sendAnswer(offer) {
				var id = this.chat_id;
				var errorHandler = function (err) {
					console.error('error', err);
				};
				console.log('check', offer)
				pc.setRemoteDescription(new RTCSessionDescription(offer), function () {
					pc.createAnswer(function (answer) {
						pc.setLocalDescription(answer, function () {
							axios.post('/chatSend/' + id, {
								video: JSON.stringify(pc.localDescription),
								action: 'answer',
							}).then(({data}) => {
								console.log('send answer')
							})
						}, errorHandler);
					}, errorHandler);
				});
			},
			openCamera() {
				navigator.mediaDevices.getUserMedia(this.type).then(stream => {
					console.log(stream.getTracks());
					streams = stream;
					stream.getTracks().forEach(function (track) {
						console.log('getTracks', track, stream);
						pc.addTrack(track, stream);
					});
					// pc.onaddstream = e => video.src = URL.createObjectURL(e.stream);
					// pc.addStream(stream);
					var video = document.getElementById('video');
					video.srcObject = stream
					localStream = stream;
					if (first) {
						this.sendOffer()
						first = false;
					}

				}).then(() => {

				});

			},
		}
		,
		created() {
			const iceConfiguration = {
				iceServers: [
					{
						url: 'turn:numb.viagenie.ca',
						credential: 'muazkh',
						username: 'webrtc@live.com'
					},
					{
						url: 'turn:192.158.29.39:3478?transport=udp',
						credential: 'JZEOEt2V3Qb0y27GRntt2u2PAYA=',
						username: '28224511:1379330808'
					},
					{
						url: 'turn:192.158.29.39:3478?transport=tcp',
						credential: 'JZEOEt2V3Qb0y27GRntt2u2PAYA=',
						username: '28224511:1379330808'
					},
					{
						url: 'turn:turn.bistri.com:80',
						credential: 'homeo',
						username: 'homeo'
					},
					{
						url: 'turn:turn.anyfirewall.com:443?transport=tcp',
						credential: 'webrtc',
						username: 'webrtc'
					}
				]
			}

			pc = new RTCPeerConnection();
			console.log(pc);
			var id = this.chat_id;
			axios.get('/chatGet/' + this.chat_id).then(({data}) => {
				console.log(data);
				this.messages = data;
			})
			;
			console.log(`chat.${this.user_id}.${this.chat_id}`);
			// Registered client on public channel to listen to MessageSent event
			Echo.private(`chat.${this.user_id}.${this.chat_id}`).listen('ChatMessage', ({message}) => {
				this.messages.push(message);
			});

			Echo.private(`video.${this.user_id}.${this.chat_id}`).listen('VideoMessage', ({message}) => {
				console.log('video', message, pc)
				switch (message[1]) {
					case 'offer_request':
						console.log('get offer')
						this.incomingCall = true;
						offers = JSON.parse(message[0]);
						break;
					case 'offer_request_audio':
						this.type = {video: false, audio: true};
						console.log('get offer audio')
						this.incomingCall = true;
						offers = JSON.parse(message[0]);
						break;
					case 'offer':
						console.log('get offer')
						// this.offer = JSON.parse(message[0]);
						this.sendAnswer(JSON.parse(message[0]));
						break;
					case 'close':
						console.log('get offer')
						document.location.reload(true);
						break;
					case 'answer':
						console.log('answer', JSON.parse(message[0]))
						pc.setRemoteDescription(JSON.parse(message[0]))
							.then(data => {
								console.log('remote setted');
								if (pc.connectionState == 'new') {
									console.log('reconn')
									this.sendOffer();
								}
							})
						break;
				}

				// this.$refs.video.srcObject = message[0]
				// this.messages.push(message);
			});
			pc.onconnectionstatechange = event => {
				console.log('state', pc.connectionState)
				if (pc.connectionState == 'connected') {
					clock();
					console.log('aeeeee')
					if (this.initial) {
						this.openCamera();
					}
					// this.openCamera();
				}
			};
			// pc.ontrack = event => {
			// 	document.getElementById("localVideo").srcObject = event.streams[0];
			// 	// document.getElementById("hangup-button").disabled = false;
			// 	console.log('event', event.streams)
			//
			// };
			pc.ontrack = ev => {
				if (ev.streams && ev.streams[0]) {
					document.getElementById("localVideo").srcObject = ev.streams[0];
				} else {
					let inboundStream = new MediaStream(ev.track);
					document.getElementById("localVideo").srcObject = inboundStream;
				}
				if (!this.initial) {
					this.openCamera();

				}
			}
		}
		,
	}

</script>