{{-- ---------------------- Image modal box ---------------------- --}}
<div id="imageModalBox" class="imageModal">
    <span class="imageModal-close">&times;</span>
    <img class="imageModal-content" id="imageModalBoxSrc">
  </div>
  
  {{-- ---------------------- Delete Modal ---------------------- --}}
  <div class="app-modal" data-name="delete">
      <div class="app-modal-container">
          <div class="app-modal-card" data-name="delete" data-modal='0'>
              <div class="app-modal-header">Are you sure you want to delete this?</div>
              <div class="app-modal-body">You can not undo this action</div>
              <div class="app-modal-footer">
                  <a href="javascript:void(0)" class="app-btn cancel">Cancel</a>
                  <a href="javascript:void(0)" class="app-btn a-btn-danger delete">Delete</a>
              </div>
          </div>
      </div>
  </div>
  {{-- ---------------------- Alert Modal ---------------------- --}}
  <div class="app-modal" data-name="alert">
      <div class="app-modal-container">
          <div class="app-modal-card" data-name="alert" data-modal='0'>
              <div class="app-modal-header"></div>
              <div class="app-modal-body"></div>
              <div class="app-modal-footer">
                  <a href="javascript:void(0)" class="app-btn cancel">Cancel</a>
              </div>
          </div>
      </div>
  </div>
  {{-- ---------------------- Settings Modal ---------------------- --}}
  <div class="app-modal" data-name="settings">
      <div class="app-modal-container">
          <div class="app-modal-card" data-name="settings" data-modal='0'>
              <form id="updateAvatar" action="{{ route('avatar.update') }}" enctype="multipart/form-data" method="POST">
                  @csrf
                  <div class="app-modal-header">Update your profile settings</div>
                  <div class="app-modal-body">
                      {{-- Udate profile avatar --}}
                      <div class="avatar av-l upload-avatar-preview"
                      style="background-image: url('{{ asset('/storage/'.config('devschat.user_avatar.folder').'/'.Auth::user()->avatar) }}');"
                      ></div>
                      <p class="upload-avatar-details"></p>
                      <label class="app-btn a-btn-primary update">
                          Upload profile photo
                          <input class="upload-avatar" accept="image/*" name="avatar" type="file" style="display: none" />
                      </label>
                      {{-- Dark/Light Mode  --}}
                      <p class="divider"></p>
                      <p class="app-modal-header">Dark Mode <span class="
                        {{ Auth::user()->dark_mode > 0 ? 'fas' : 'far' }} fa-moon dark-mode-switch"
                         data-mode="{{ Auth::user()->dark_mode > 0 ? 1 : 0 }}"></span></p>
                      {{-- change messenger color  --}}
                      <p class="divider"></p>
                      <p class="app-modal-header">Change {{ config('devschat.name') }} Color</p>
                      <div class="update-messengerColor">
                            <a href="javascript:void(0)" class="messengerColor-1"></a>
                            <a href="javascript:void(0)" class="messengerColor-2"></a>
                            <a href="javascript:void(0)" class="messengerColor-3"></a>
                            <a href="javascript:void(0)" class="messengerColor-4"></a>
                            <a href="javascript:void(0)" class="messengerColor-5"></a>
                            <br/>
                            <a href="javascript:void(0)" class="messengerColor-6"></a>
                            <a href="javascript:void(0)" class="messengerColor-7"></a>
                            <a href="javascript:void(0)" class="messengerColor-8"></a>
                            <a href="javascript:void(0)" class="messengerColor-9"></a>
                            <a href="javascript:void(0)" class="messengerColor-10"></a>
                      </div>
                  </div>
                  <div class="app-modal-footer">
                      <a href="javascript:void(0)" class="app-btn cancel">Cancel</a>
                      <input type="submit" class="app-btn a-btn-success update" value="Update" />
                  </div>
              </form>
          </div>
      </div>
  </div>

  {{-- ---------------------- Create Group Modal ---------------------- --}}
  <div class="app-modal" data-name="createGroup">
      <div class="app-modal-container">
          <div class="app-modal-card" data-name="createGroup" data-modal='0'>
              <form id="createGroupForm">
                  @csrf
                  <div class="app-modal-header">Create New Group</div>
                  <div class="app-modal-body">
                      <div class="form-group">
                          <label for="groupName">Group Name *</label>
                          <input type="text" class="form-control" id="groupName" name="name" required maxlength="255" style="width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px;">
                      </div>
                      <div class="form-group">
                          <label for="groupDescription">Description</label>
                          <textarea class="form-control" id="groupDescription" name="description" rows="3" maxlength="1000" style="width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                      </div>
                      <div class="form-group">
                          <label for="groupMembers">Select Members *</label>
                          <select class="form-control" id="groupMembers" name="members[]" multiple required style="width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px;">
                              @foreach($users as $user)
                                  <option value="{{ $user->id }}">{{ $user->name }}</option>
                              @endforeach
                          </select>
                          <small style="color: #666; font-size: 12px;">Hold Ctrl/Cmd to select multiple members</small>
                      </div>
                      <div class="form-check">
                          <input type="checkbox" id="isPrivate" name="is_private">
                          <label for="isPrivate" style="margin-left: 5px;">Private Group</label>
                      </div>
                  </div>
                  <div class="app-modal-footer">
                      <a href="javascript:void(0)" class="app-btn cancel">Cancel</a>
                      <button type="submit" class="app-btn a-btn-success">Create Group</button>
                  </div>
              </form>
          </div>
      </div>
  </div>

  {{-- ---------------------- Group Info Modal ---------------------- --}}
  <div class="app-modal" data-name="groupInfo">
      <div class="app-modal-container">
          <div class="app-modal-card" data-name="groupInfo" data-modal='0'>
              <div class="app-modal-header">Group Information</div>
              <div class="app-modal-body" id="groupInfoContent">
                  <!-- Group info will be loaded here -->
              </div>
              <div class="app-modal-footer">
                  <a href="javascript:void(0)" class="app-btn cancel">Close</a>
              </div>
          </div>
      </div>
  </div>