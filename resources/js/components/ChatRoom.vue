<template>
    <div>
    <div class="panel-heading">
        <span class="glyphicon glyphicon-comment"></span> Chat with test

        <button class="btn btn-warning btn-sm pull-right" @click="startVideoCallToUser(2)" type="button">
            <span class="fa fa-video-camera"></span> Video Call
        </button>
    </div>
    <div class="col-md-6">
        <video-box></video-box>
    </div>
    <div id="incomingVideoCallModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Incoming Call</h4>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="answerCall" id="answerCallButton" class="btn btn-success">Answer</button>
                    <button type="button" id="denyCallButton" data-dismiss="modal" class="btn btn-danger">Deny</button>
                </div>
            </div>

        </div>
    </div>
    </div>


</template>
