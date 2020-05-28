<template>
    <div>
        <div>
            <p v-for="message in messages">{{ message }}</p>
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
    </div>
</template>

<script>
	var pc = new RTCPeerConnection();
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
				messages: []
			}
		},
		computed: {
			contentExists() {
				return this.text.length > 0;
			}
		},
		methods: {
			postMessage() {
				axios.post('/chatSend/' + this.chat_id, {message: this.text,}).then(({data}) => {
					this.messages.push(this.text);
					console.log('send text', data)
					this.text = '';
				})
				;
			},
			startVideoCallToUser() {
				var errorHandler = function (err) {
					console.error('error', err);
				};
				var id = this.chat_id;


				navigator.mediaDevices.getUserMedia({video: true}).then(stream => {
					// pc.onaddstream = e => video.src = URL.createObjectURL(e.stream);
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
					// console.log(stream)
					// this.video.srcObject = stream;
					console.log(stream)
					this.$refs.video.srcObject = stream
					// this.video.play();
					// console.log(this.video, pc);
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
				console.log('omg', message)
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
							console.log('answer 2')
							axios.post('/chatSend/' + id, {video: JSON.stringify(pc.localDescription)}).then(({data}) => {
								console.log('send 23')
							})
						}, errorHandler);
					}, errorHandler);
				});
			});
		}
	}

</script>