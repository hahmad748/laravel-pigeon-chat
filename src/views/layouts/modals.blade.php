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
              <form id="createGroupForm" onsubmit="console.log('Form onsubmit triggered!'); return false;">
                  @csrf
                  <div class="app-modal-header">
                      <i class="fas fa-users" style="margin-right: 10px; color: #2180f3;"></i>
                      Create New Group
                  </div>
                  <div class="app-modal-body">
                      <div class="form-group">
                          <label for="groupName" class="form-label">
                              <i class="fas fa-tag" style="margin-right: 5px; color: #666;"></i>
                              Group Name *
                          </label>
                          <input type="text" 
                                 class="form-control modern-input" 
                                 id="groupName" 
                                 name="name" 
                                 required 
                                 maxlength="255" 
                                 placeholder="Enter group name...">
                      </div>
                      
                      <div class="form-group">
                          <label for="groupDescription" class="form-label">
                              <i class="fas fa-align-left" style="margin-right: 5px; color: #666;"></i>
                              Description
                          </label>
                          <textarea class="form-control modern-textarea" 
                                    id="groupDescription" 
                                    name="description" 
                                    rows="3" 
                                    maxlength="1000" 
                                    placeholder="Describe your group..."></textarea>
                      </div>
                      
                      <div class="form-group">
                          <label for="groupMembers" class="form-label">
                              <i class="fas fa-user-plus" style="margin-right: 5px; color: #666;"></i>
                              Select Members *
                          </label>
                          <div class="members-selector">
                              <select class="form-control modern-select" 
                                      id="groupMembers" 
                                      name="members[]" 
                                      multiple 
                                      required>
                                  @foreach($users as $user)
                                      <option value="{{ $user->id }}" class="member-option">
                                          <i class="fas fa-user"></i> {{ $user->name }}
                                      </option>
                                  @endforeach
                              </select>
                              <div class="selected-members" id="selectedMembers"></div>
                          </div>
                          <small class="form-help">
                              <i class="fas fa-info-circle"></i>
                              Hold Ctrl/Cmd to select multiple members
                          </small>
                      </div>
                      
                      <div class="form-group">
                          <div class="form-check modern-checkbox">
                              <input type="checkbox" id="isPrivate" name="is_private" class="modern-checkbox-input">
                              <label for="isPrivate" class="modern-checkbox-label">
                                  <i class="fas fa-lock" style="margin-right: 5px;"></i>
                                  Private Group
                              </label>
                          </div>
                          <small class="form-help">
                              <i class="fas fa-shield-alt"></i>
                              Private groups are only visible to members
                          </small>
                      </div>
                  </div>
                  <div class="app-modal-footer">
                      <a href="javascript:void(0)" class="app-btn cancel modern-btn-cancel">
                          <i class="fas fa-times"></i> Cancel
                      </a>
                      <button type="submit" class="app-btn a-btn-success modern-btn-create">
                          <i class="fas fa-plus"></i> Create Group
                      </button>
                      <button type="button" class="app-btn" style="background: #9b59b6; color: white; margin-left: 10px;" onclick="console.log('Test button clicked!'); alert('JavaScript is working!');">
                          <i class="fas fa-bug"></i> Test JS
                      </button>
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