-- Bảng người dùng (Users)
CREATE TABLE Users (
    user_id BIGINT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20),
    full_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender VARCHAR(20),
    profile_picture_url VARCHAR(255),
    cover_photo_url VARCHAR(255),
    bio TEXT,
    location VARCHAR(100),
    website VARCHAR(255),
    join_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP,
    is_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    account_status VARCHAR(20) DEFAULT 'active',
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    language_preference VARCHAR(10) DEFAULT 'en_US',
    timezone VARCHAR(50),
    notification_settings JSONB
);

-- Bảng thông tin cá nhân mở rộng (User_Details)
CREATE TABLE User_Details (
    user_detail_id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES Users(user_id),
    workplace VARCHAR(255),
    education JSONB,
    relationship_status VARCHAR(50),
    interested_in VARCHAR(50),
    political_views VARCHAR(100),
    religious_views VARCHAR(100),
    languages JSONB,
    favorite_quotes TEXT,
    about_me TEXT,
    nickname VARCHAR(100),
    significant_events JSONB
);

-- Bảng bạn bè (Friendships)
CREATE TABLE Friendships (
    friendship_id BIGINT PRIMARY KEY,
    user_id_1 BIGINT REFERENCES Users(user_id),
    user_id_2 BIGINT REFERENCES Users(user_id),
    status VARCHAR(20) NOT NULL, -- 'pending', 'accepted', 'declined', 'blocked'
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    action_date TIMESTAMP,
    is_close_friend BOOLEAN DEFAULT FALSE,
    UNIQUE(user_id_1, user_id_2)
);

-- Bảng bài đăng (Posts)
CREATE TABLE Posts (
    post_id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES Users(user_id),
    content TEXT,
    post_type VARCHAR(20) NOT NULL, -- 'status', 'photo', 'video', 'link', 'event', 'shared'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    privacy_setting VARCHAR(20) NOT NULL DEFAULT 'public', -- 'public', 'friends', 'private', 'custom'
    location VARCHAR(255),
    feeling VARCHAR(100),
    activity VARCHAR(100),
    is_pinned BOOLEAN DEFAULT FALSE,
    allow_comments BOOLEAN DEFAULT TRUE,
    original_post_id BIGINT REFERENCES Posts(post_id), -- Cho bài đăng được chia sẻ
    group_id BIGINT, -- Sẽ được tham chiếu đến bảng Groups
    page_id BIGINT -- Sẽ được tham chiếu đến bảng Pages
);

-- Bảng đa phương tiện (Media)
CREATE TABLE Media (
    media_id BIGINT PRIMARY KEY,
    post_id BIGINT REFERENCES Posts(post_id),
    user_id BIGINT REFERENCES Users(user_id),
    media_type VARCHAR(20) NOT NULL, -- 'image', 'video', 'audio', 'document'
    media_url VARCHAR(255) NOT NULL,
    thumbnail_url VARCHAR(255),
    caption TEXT,
    alt_text TEXT,
    width INT,
    height INT,
    duration INT, -- For videos or audio (seconds)
    size BIGINT, -- file size in bytes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng thích (Likes)
CREATE TABLE Likes (
    like_id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES Users(user_id),
    content_type VARCHAR(20) NOT NULL, -- 'post', 'comment', 'photo', 'video'
    content_id BIGINT NOT NULL, -- ID của post, comment, etc.
    reaction_type VARCHAR(20) NOT NULL DEFAULT 'like', -- 'like', 'love', 'haha', 'wow', 'sad', 'angry'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, content_type, content_id)
);

-- Bảng bình luận (Comments)
CREATE TABLE Comments (
    comment_id BIGINT PRIMARY KEY,
    post_id BIGINT REFERENCES Posts(post_id),
    user_id BIGINT REFERENCES Users(user_id),
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    parent_comment_id BIGINT REFERENCES Comments(comment_id), -- Cho các trả lời bình luận
    is_hidden BOOLEAN DEFAULT FALSE,
    attachment_url VARCHAR(255)
);

-- Bảng nhóm (Groups)
CREATE TABLE Groups (
    group_id BIGINT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    creator_id BIGINT REFERENCES Users(user_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    privacy_setting VARCHAR(20) NOT NULL, -- 'public', 'closed', 'secret'
    cover_photo_url VARCHAR(255),
    group_type VARCHAR(50), -- 'general', 'buy_sell', 'gaming', etc.
    location VARCHAR(255),
    rules TEXT,
    post_approval_required BOOLEAN DEFAULT FALSE,
    is_archived BOOLEAN DEFAULT FALSE
);

-- Bảng thành viên nhóm (Group_Members)
CREATE TABLE Group_Members (
    group_member_id BIGINT PRIMARY KEY,
    group_id BIGINT REFERENCES Groups(group_id),
    user_id BIGINT REFERENCES Users(user_id),
    role VARCHAR(20) NOT NULL, -- 'admin', 'moderator', 'member'
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    invited_by BIGINT REFERENCES Users(user_id),
    membership_status VARCHAR(20) NOT NULL, -- 'active', 'pending', 'blocked'
    notifications_enabled BOOLEAN DEFAULT TRUE,
    UNIQUE(group_id, user_id)
);

-- Bảng trang (Pages)
CREATE TABLE Pages (
    page_id BIGINT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(100) UNIQUE,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    profile_picture_url VARCHAR(255),
    cover_photo_url VARCHAR(255),
    website VARCHAR(255),
    phone VARCHAR(20),
    email VARCHAR(255),
    location JSONB,
    hours_of_operation JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified BOOLEAN DEFAULT FALSE,
    follower_count BIGINT DEFAULT 0
);

-- Bảng quản trị viên trang (Page_Admins)
CREATE TABLE Page_Admins (
    page_admin_id BIGINT PRIMARY KEY,
    page_id BIGINT REFERENCES Pages(page_id),
    user_id BIGINT REFERENCES Users(user_id),
    role VARCHAR(50) NOT NULL, -- 'admin', 'editor', 'moderator', 'analyst', 'advertiser'
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(page_id, user_id)
);

-- Bảng theo dõi trang (Page_Followers)
CREATE TABLE Page_Followers (
    page_follower_id BIGINT PRIMARY KEY,
    page_id BIGINT REFERENCES Pages(page_id),
    user_id BIGINT REFERENCES Users(user_id),
    followed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notification_settings VARCHAR(20) DEFAULT 'all', -- 'all', 'highlights', 'none'
    UNIQUE(page_id, user_id)
);

-- Bảng sự kiện (Events)
CREATE TABLE Events (
    event_id BIGINT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP,
    location JSONB, -- Địa chỉ cụ thể, tọa độ, etc.
    creator_id BIGINT REFERENCES Users(user_id),
    page_id BIGINT REFERENCES Pages(page_id),
    group_id BIGINT REFERENCES Groups(group_id),
    cover_photo_url VARCHAR(255),
    privacy_setting VARCHAR(20) NOT NULL, -- 'public', 'private', 'friends'
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    is_online BOOLEAN DEFAULT FALSE,
    online_meeting_link VARCHAR(255),
    ticket_url VARCHAR(255)
);

-- Bảng tham dự sự kiện (Event_Attendees)
CREATE TABLE Event_Attendees (
    event_attendee_id BIGINT PRIMARY KEY,
    event_id BIGINT REFERENCES Events(event_id),
    user_id BIGINT REFERENCES Users(user_id),
    response_status VARCHAR(20) NOT NULL, -- 'attending', 'maybe', 'declined', 'invited'
    response_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    invited_by BIGINT REFERENCES Users(user_id),
    UNIQUE(event_id, user_id)
);

-- Bảng tin nhắn (Messages)
CREATE TABLE Messages (
    message_id BIGINT PRIMARY KEY,
    sender_id BIGINT REFERENCES Users(user_id),
    conversation_id BIGINT NOT NULL, -- Tham chiếu đến bảng Conversations
    content TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP,
    message_type VARCHAR(20) NOT NULL, -- 'text', 'image', 'video', 'file', 'location', 'audio'
    attachment_url VARCHAR(255),
    is_deleted BOOLEAN DEFAULT FALSE,
    is_forwarded BOOLEAN DEFAULT FALSE,
    original_message_id BIGINT REFERENCES Messages(message_id),
    reply_to_message_id BIGINT REFERENCES Messages(message_id)
);

-- Bảng cuộc trò chuyện (Conversations)
CREATE TABLE Conversations (
    conversation_id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    conversation_type VARCHAR(20) NOT NULL, -- 'individual', 'group'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    creator_id BIGINT REFERENCES Users(user_id),
    is_active BOOLEAN DEFAULT TRUE,
    theme VARCHAR(50),
    emoji VARCHAR(50)
);

-- Bảng thành viên cuộc trò chuyện (Conversation_Members)
CREATE TABLE Conversation_Members (
    conversation_member_id BIGINT PRIMARY KEY,
    conversation_id BIGINT REFERENCES Conversations(conversation_id),
    user_id BIGINT REFERENCES Users(user_id),
    nickname VARCHAR(100),
    role VARCHAR(20) DEFAULT 'member', -- 'admin', 'member'
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_read_message_id BIGINT REFERENCES Messages(message_id),
    mute_until TIMESTAMP,
    is_favorite BOOLEAN DEFAULT FALSE,
    notifications_enabled BOOLEAN DEFAULT TRUE,
    UNIQUE(conversation_id, user_id)
);

-- Bảng thông báo (Notifications)
CREATE TABLE Notifications (
    notification_id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES Users(user_id),
    type VARCHAR(50) NOT NULL, -- 'friend_request', 'comment', 'like', 'tag', 'event', 'group_invite', etc.
    reference_id BIGINT NOT NULL, -- ID của đối tượng tham chiếu (post, comment, etc.)
    reference_type VARCHAR(50) NOT NULL, -- 'post', 'comment', 'friendship', etc.
    sender_id BIGINT REFERENCES Users(user_id),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP,
    action_url VARCHAR(255)
);

-- Bảng hashtags (Hashtags)
CREATE TABLE Hashtags (
    hashtag_id BIGINT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    post_count BIGINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng liên kết hashtag với bài đăng (Post_Hashtags)
CREATE TABLE Post_Hashtags (
    post_hashtag_id BIGINT PRIMARY KEY,
    post_id BIGINT REFERENCES Posts(post_id),
    hashtag_id BIGINT REFERENCES Hashtags(hashtag_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(post_id, hashtag_id)
);

-- Bảng gắn thẻ người dùng (User_Tags)
CREATE TABLE User_Tags (
    user_tag_id BIGINT PRIMARY KEY,
    post_id BIGINT REFERENCES Posts(post_id),
    comment_id BIGINT REFERENCES Comments(comment_id),
    media_id BIGINT REFERENCES Media(media_id),
    tagger_id BIGINT REFERENCES Users(user_id),
    tagged_user_id BIGINT REFERENCES Users(user_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'pending', -- 'pending', 'approved', 'rejected'
    x_position DECIMAL, -- Vị trí X trong ảnh (0-100%)
    y_position DECIMAL -- Vị trí Y trong ảnh (0-100%)
);

-- Bảng lưu trữ (Saved_Items)
CREATE TABLE Saved_Items (
    saved_item_id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES Users(user_id),
    content_type VARCHAR(20) NOT NULL, -- 'post', 'article', 'video', 'product', etc.
    content_id BIGINT NOT NULL,
    collection_id BIGINT, -- Tham chiếu đến bảng Collections
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT
);

-- Bảng bộ sưu tập (Collections)
CREATE TABLE Collections (
    collection_id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES Users(user_id),
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    is_default BOOLEAN DEFAULT FALSE,
    privacy_setting VARCHAR(20) DEFAULT 'private' -- 'private', 'public', 'friends'
);

-- Bảng Stories
CREATE TABLE Stories (
    story_id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES Users(user_id),
    media_url VARCHAR(255) NOT NULL,
    media_type VARCHAR(20) NOT NULL, -- 'image', 'video'
    caption TEXT,
    filter_used VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP, -- 24 giờ sau created_at
    is_highlighted BOOLEAN
);
