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
        <button class="btn btn-warning btn-sm pull-right" @click="startVideoCallToUser(2)" type="button">
            <span class="fa fa-video-camera"></span> Video Call
        </button>
        <button class="btn btn-warning btn-sm pull-right" @click="startAudioCallToUser(2)" type="button">
            <span class="fa fa-video-camera"></span> Audio Call
        </button>
        <button class="btn btn-warning btn-sm pull-right" @click="closeCall(2)" type="button">
            <span class="fa fa-video-camera"></span> End Call
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
	var timer;

	var pc = new RTCPeerConnection();

	var streams;
	var localStream;
	const offerOptions = {
		offerToReceiveAudio: 1,
		offerToReceiveVideo: 1
	};
	var connect = false;
	pc.onaddstream = function (event) {
		document.getElementById("localVideo").srcObject = event.stream;
	};
	// pc.ontrack = function (event) {
	// 	document.getElementById("localVideo").srcObject = event.streams[0];
	// 	// document.getElementById("hangup-button").disabled = false;
	// 	console.log('event', event.streams)
	// };
	pc.onconnectionstatechange = function (event) {
		console.log('state', pc.connectionState)
		if (pc.connectionState == 'connected') {
			timer = setInterval(function () {
				seconds = seconds + 1;
				console.log('time', seconds);
			}, 1000)
			timer();
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
				pc.close()
				axios.post('/chatSend/' + this.chat_id, {message: 'Звонок длительностью ' + seconds + 'с',}).then(({data}) => {
					this.messages.push(data);
					console.log('send close', data)
				})
				clearInterval(timer);
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
				this.openCamera();
				// this.sendOffer();

			},
			startAudioCallToUser() {
				this.openAudio();
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
			sendOfferAudio() {
				var id = this.chat_id;
				pc.createOffer(offerOptions).then(function (offer) {
					return pc.setLocalDescription(offer);
				})
					.then(function () {
						axios.post('/chatSend/' + id, {
							video: JSON.stringify(pc.localDescription),
							action: 'offer_audio',
						}).then(({data}) => {
							console.log('send offer')
						})
					})
			},
			sendAnswer(offer) {
				var id = this.chat_id;
				var errorHandler = function (err) {
					console.error('error', err);
				};
				pc.setRemoteDescription(new RTCSessionDescription(offer), function () {
					pc.createAnswer(function (answer) {
						pc.setLocalDescription(answer, function () {
							axios.post('/chatSend/' + id, {
								video: JSON.stringify(pc.localDescription),
								action: 'answer',
							}).then(({data}) => {
								console.log(pc.localDescription)
							})
						}, errorHandler);
					}, errorHandler);
				});
			},
			sendAnswerAudio(offer) {
				var id = this.chat_id;
				var errorHandler = function (err) {
					console.error('error', err);
				};
				pc.setRemoteDescription(new RTCSessionDescription(offer), function () {
					pc.createAnswer(function (answer) {
						pc.setLocalDescription(answer, function () {
							axios.post('/chatSend/' + id, {
								video: JSON.stringify(pc.localDescription),
								action: 'answer_audio',
							}).then(({data}) => {
								console.log(pc.localDescription)
							})
						}, errorHandler);
					}, errorHandler);
				});
			},
			openCamera() {
				navigator.mediaDevices.getUserMedia({video: true, audio: true}).then(stream => {
					streams = stream;
					stream.getTracks().forEach(function (track) {
						console.log('getTracks', track, stream);
						pc.addTrack(track, stream);
					});
					var video = document.getElementById('video');
					video.srcObject = stream
					localStream = stream;
				}).then(() => {
					this.sendOffer()
				});

			},
			openCamera2() {
				navigator.mediaDevices.getUserMedia({video: true, audio: true}).then(stream => {
					// this.timer();
					streams = stream;
					stream.getTracks().forEach(function (track) {
						console.log('getTracks', track, stream);
						pc.addTrack(track, stream);
					});
					var video = document.getElementById('video');
					video.srcObject = stream
					localStream = stream;
					if (!connect) {
						this.sendOffer()
						connect = true;
					} else {
						connect = true;
					}
				}).catch(err => {
					console.log('err', err)
				});
			},
			openAudio() {
				navigator.mediaDevices.getUserMedia({video: false, audio: true}).then(stream => {
					streams = stream;
					stream.getTracks().forEach(function (track) {
						console.log('getTracks', track, stream);
						pc.addTrack(track, stream);
					});
				}).then(() => {
					this.sendOfferAudio()
				});
			},
			openAudio2() {
				navigator.mediaDevices.getUserMedia({video: false, audio: true}).then(stream => {
					// this.timer();
					streams = stream;
					stream.getTracks().forEach(function (track) {
						console.log('getTracks', track, stream);
						pc.addTrack(track, stream);
					});
					if (!connect) {
						this.sendOfferAudio()
						connect = true;
					} else {
						connect = true;
					}
				}).catch(err => {
					console.log('err', err)
				});
			},

		}
		,
		created() {
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
					case 'offer':
						console.log('get offer')
						this.openCamera2();
						var offer = JSON.parse(message[0]);
						this.sendAnswer(offer);
						break;
					case 'offer_audio':
						console.log('get offer audio')
						this.openAudio();
						var offer = JSON.parse(message[0]);
						this.sendAnswerAudio(offer);
						break;
					case 'answer':
						pc.setRemoteDescription(JSON.parse(message[0]))
							.then(data => {
								console.log(pc);
							})
						this.openCamera2()
					case 'answer_audio':
						pc.setRemoteDescription(JSON.parse(message[0]))
							.then(data => {
								console.log(pc);
							})
						this.openAudio2()
				}

				// this.$refs.video.srcObject = message[0]
				// this.messages.push(message);


			});
		}
		,
	}

</script>