<template>
    <div>
        <div>
            <p v-for="message in messages">{{ message.content }}</p>
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
	var pc = new RTCPeerConnection();
	var seconds = 0;
	var streams;

	pc.onaddstream = function (obj) {
		console.log('gg', obj);
		var video = document.getElementById('localVideo');
		video.srcObject = obj.stream;
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
			},
			timer() {
				seconds = 0;
				setInterval(function () {
					seconds = seconds + 1;
					this.seconds = seconds + 1;
					console.log('time', seconds);
				}, 1000);
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
				var errorHandler = function (err) {
					console.error('error', err);
				};
				var id = this.chat_id;
				navigator.mediaDevices.getUserMedia({video: true, audio: true}).then(stream => {
					this.timer();
					// pc.onaddstream = e => video.src = URL.createObjectURL(e.stream);
					streams = stream;
					stream.getTracks().forEach(function (track) {
						console.log(track, stream);
						pc.addTrack(track, stream);
					});

					pc.createOffer(function (offer) {
						pc.setLocalDescription(offer, function () {
							console.log('offer', pc.localDescription);
							axios.post('/chatSend/' + id, {video: JSON.stringify(pc.localDescription)}).then(({data}) => {
								console.log('send')
							})
						}, errorHandler);
					}, errorHandler);
					this.$refs.video.srcObject = stream
				});
			},
			startAudioCallToUser() {
				var errorHandler = function (err) {
					console.error('error', err);
				};
				var id = this.chat_id;
				navigator.mediaDevices.getUserMedia({audio: true}).then(stream => {
					this.timer();
					// pc.onaddstream = e => video.src = URL.createObjectURL(e.stream);
					streams = stream;
					stream.getTracks().forEach(function (track) {
						console.log(track, stream);
						pc.addTrack(track, stream);
					});

					pc.createOffer(function (offer) {
						pc.setLocalDescription(offer, function () {
							console.log('offer', pc.localDescription);
							axios.post('/chatSend/' + id, {video: JSON.stringify(pc.localDescription)}).then(({data}) => {
								console.log('send')
							})
						}, errorHandler);
					}, errorHandler);
					this.$refs.video.srcObject = stream
				});
			}

		},
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
				console.log('video', message)
				// this.$refs.video.srcObject = message
				// this.messages.push(message);
				var offer = JSON.parse(message);
				var errorHandler = function (err) {
					console.error('error', err);
				};


				pc.setRemoteDescription(new RTCSessionDescription(offer), function () {
					pc.createAnswer(function (answer) {
						pc.setLocalDescription(answer, function () {
							axios.post('/chatSend/' + id, {video: JSON.stringify(pc.localDescription)}).then(({data}) => {
								console.log('send 23')
							})
						}, errorHandler);
					}, errorHandler);
				});
			});
		},
	}

</script>