<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleRevision;
use App\Models\Category;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categoryData = [
            ['Thời sự', 'thoi-su', 'Tin tức trong nước, chính sách và các vấn đề xã hội.', 1],
            ['Thế giới', 'the-gioi', 'Diễn biến quốc tế và những câu chuyện toàn cầu.', 2],
            ['Kinh doanh', 'kinh-doanh', 'Kinh tế, thị trường, doanh nghiệp và khởi nghiệp.', 3],
            ['Công nghệ', 'cong-nghe', 'Công nghệ, đổi mới sáng tạo và đời sống số.', 4],
            ['Thể thao', 'the-thao', 'Thể thao trong nước và quốc tế.', 5],
            ['Giải trí', 'giai-tri', 'Văn hóa, nghệ thuật và giải trí.', 6],
            ['Sức khỏe', 'suc-khoe', 'Sức khỏe cộng đồng và lối sống lành mạnh.', 7],
            ['Giáo dục', 'giao-duc', 'Giáo dục, tri thức và phát triển kỹ năng.', 8],
            ['Pháp luật', 'phap-luat', 'Pháp luật, chính sách và kiến thức công dân.', 9],
            ['Đời sống', 'doi-song', 'Gia đình, cộng đồng và những câu chuyện thường ngày.', 10],
        ];

        $categories = [];
        foreach ($categoryData as [$name, $slug, $description, $order]) {
            $categories[$slug] = Category::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'description' => $description, 'is_active' => true, 'sort_order' => $order,
            ]);
        }

        // Never seed demo identities into production unless explicitly enabled by the operator.
        if (app()->environment('production') && !filter_var(env('ALLOW_DEMO_SEED', false), FILTER_VALIDATE_BOOLEAN)) {
            return;
        }
        if (!filter_var(env('ALLOW_DEMO_SEED', true), FILTER_VALIDATE_BOOLEAN)) return;

        $admin = $this->demoUser('SEED_ADMIN_EMAIL', 'admin@news.local', 'SEED_ADMIN_PASSWORD', 'Admin@12345', 'Nguyễn Minh Anh', 'ADMIN');
        $editor1 = $this->demoUser('SEED_EDITOR1_EMAIL', 'editor1@news.local', 'SEED_EDITOR1_PASSWORD', 'Editor@12345', 'Trần Khánh Linh', 'EDITOR');
        $editor2 = $this->demoUser('SEED_EDITOR2_EMAIL', 'editor2@news.local', 'SEED_EDITOR2_PASSWORD', 'Editor@12345', 'Lê Hoàng Nam', 'EDITOR');
        $reader1 = $this->demoUser('SEED_USER1_EMAIL', 'reader1@news.local', 'SEED_USER1_PASSWORD', 'User@12345', 'Phạm Thu Hà', 'USER');
        $reader2 = $this->demoUser('SEED_USER2_EMAIL', 'reader2@news.local', 'SEED_USER2_PASSWORD', 'User@12345', 'Đỗ Đức Thành', 'USER');
        $reader3 = $this->demoUser('SEED_USER3_EMAIL', 'reader3@news.local', 'SEED_USER3_PASSWORD', 'User@12345', 'Vũ Ngọc Mai', 'USER');

        $published = [
            [
                'slug' => 'khong-gian-xanh-trong-do-thi', 'category' => 'thoi-su', 'author' => $editor1,
                'title' => 'Không gian xanh trong đô thị: Khi những khoảng thở trở thành hạ tầng thiết yếu',
                'excerpt' => 'Từ công viên nhỏ đến hành lang cây xanh, các đô thị đang nhìn nhận không gian công cộng như một phần quan trọng của chất lượng sống.',
                'body' => '<p>Trong nhịp phát triển nhanh của các đô thị, không gian xanh ngày càng được nhìn nhận như một hạ tầng thiết yếu. Những khoảng cây xanh không chỉ làm dịu bầu không khí mà còn tạo nơi gặp gỡ, vận động và nghỉ ngơi cho cộng đồng.</p><h2>Thiết kế từ nhu cầu của cư dân</h2><p>Một công viên hiệu quả không nhất thiết phải có diện tích lớn. Điều quan trọng là khả năng tiếp cận, bóng mát, lối đi an toàn và các tiện ích phù hợp với nhiều nhóm tuổi. Khi được kết nối thành mạng lưới, những không gian nhỏ có thể tạo nên thay đổi đáng kể.</p><blockquote>Không gian công cộng tốt là nơi mọi người cảm thấy mình thuộc về thành phố.</blockquote><h2>Từ quy hoạch đến vận hành</h2><p>Cây xanh cần được chăm sóc, hệ thống chiếu sáng và thoát nước cần được duy trì. Vì vậy, cộng đồng cư dân nên tham gia từ giai đoạn lên ý tưởng đến quá trình giám sát vận hành.</p><p><strong>Ghi chú:</strong> Đây là nội dung minh họa được tạo cho môi trường kiểm thử, không phải bản tin sự kiện có thật.</p>',
            ],
            [
                'slug' => 'doanh-nghiep-nho-so-hoa-van-hanh', 'category' => 'kinh-doanh', 'author' => $editor2,
                'title' => 'Doanh nghiệp nhỏ bắt đầu chuyển đổi số từ những quy trình rất gần',
                'excerpt' => 'Không phải dự án công nghệ lớn, việc số hóa hóa đơn, quản lý kho và chăm sóc khách hàng thường là bước đi thực tế nhất.',
                'body' => '<p>Với doanh nghiệp nhỏ, chuyển đổi số thường bắt đầu từ câu hỏi đơn giản: công việc nào đang tốn nhiều thời gian nhất và có thể được chuẩn hóa? Câu trả lời có thể là quản lý đơn hàng, theo dõi tồn kho hoặc lưu trữ hồ sơ.</p><h2>Ưu tiên quy trình trước công cụ</h2><p>Doanh nghiệp nên mô tả quy trình hiện tại, tìm điểm nghẽn và thống nhất cách nhập dữ liệu. Sau đó mới chọn phần mềm phù hợp với quy mô và khả năng vận hành.</p><ul><li>Chuẩn hóa dữ liệu khách hàng.</li><li>Thiết lập phân quyền tài khoản.</li><li>Sao lưu dữ liệu định kỳ.</li><li>Đánh giá hiệu quả sau từng giai đoạn.</li></ul><p>Chuyển đổi số bền vững cần sự tham gia của nhân viên và kế hoạch đào tạo rõ ràng.</p>',
            ],
            [
                'slug' => 'tri-tue-nhan-tao-va-lop-hoc-mo', 'category' => 'giao-duc', 'author' => $editor1,
                'title' => 'Trí tuệ nhân tạo trong lớp học: Cơ hội mới đi cùng yêu cầu tư duy phản biện',
                'excerpt' => 'Công cụ AI có thể hỗ trợ học tập, nhưng người học vẫn cần biết kiểm tra nguồn, đánh giá kết quả và chịu trách nhiệm với sản phẩm của mình.',
                'body' => '<p>Các công cụ trí tuệ nhân tạo tạo sinh mở ra nhiều cách tiếp cận kiến thức. Học sinh có thể dùng công cụ để gợi ý cấu trúc bài học, giải thích khái niệm hoặc tạo câu hỏi ôn tập.</p><h2>AI là công cụ, không phải người làm thay</h2><p>Giáo viên có thể thiết kế nhiệm vụ yêu cầu học sinh trình bày cách kiểm chứng thông tin, so sánh nhiều nguồn và nêu phần đóng góp của bản thân. Cách tiếp cận này biến AI thành chủ đề để học về tư duy phản biện.</p><p>Nhà trường cũng cần quy định rõ dữ liệu nào không được đưa lên dịch vụ bên ngoài, cách trích dẫn sự hỗ trợ của AI và trách nhiệm khi sử dụng nội dung sai lệch.</p>',
            ],
            [
                'slug' => 'an-toan-tai-khoan-trong-moi-truong-so', 'category' => 'cong-nghe', 'author' => $editor2,
                'title' => 'An toàn tài khoản trong môi trường số: Những thói quen nhỏ tạo khác biệt lớn',
                'excerpt' => 'Mật khẩu riêng biệt, xác thực đa yếu tố và cảnh giác với liên kết lạ là những lớp bảo vệ cơ bản cho tài khoản cá nhân.',
                'body' => '<p>Một tài khoản trực tuyến thường liên kết với nhiều dịch vụ khác nhau. Vì vậy, việc sử dụng lại cùng một mật khẩu có thể khiến một sự cố nhỏ lan rộng sang nhiều nền tảng.</p><h2>Ba việc nên làm ngay</h2><ol><li>Dùng trình quản lý mật khẩu để tạo mật khẩu dài và riêng biệt.</li><li>Bật xác thực đa yếu tố, ưu tiên ứng dụng xác thực hoặc khóa bảo mật.</li><li>Kiểm tra địa chỉ website trước khi nhập thông tin đăng nhập.</li></ol><p>Ngoài ra, người dùng nên rà soát các phiên đăng nhập và thu hồi thiết bị không còn sử dụng.</p>',
            ],
            [
                'slug' => 'the-thao-hoc-duong-va-suc-khoe', 'category' => 'the-thao', 'author' => $editor1,
                'title' => 'Thể thao học đường: Tạo thói quen vận động thay vì chỉ chạy theo thành tích',
                'excerpt' => 'Một chương trình vận động hiệu quả cần khuyến khích mọi học sinh tham gia, tôn trọng khác biệt và duy trì niềm vui trong luyện tập.',
                'body' => '<p>Hoạt động thể thao ở trường học có thể góp phần xây dựng sức khỏe thể chất, tinh thần đồng đội và khả năng tự quản lý. Tuy nhiên, mục tiêu quan trọng không chỉ nằm ở huy chương hay thứ hạng.</p><h2>Đa dạng hình thức vận động</h2><p>Đi bộ, chạy nhẹ, bóng rổ, cầu lông và các trò chơi vận động đều có thể tạo cơ hội cho học sinh tham gia. Giáo viên cần điều chỉnh cường độ theo độ tuổi và điều kiện sức khỏe.</p><p>Một môi trường an toàn, vui vẻ giúp các em duy trì thói quen vận động lâu dài.</p>',
            ],
            [
                'slug' => 'van-hoa-doc-trong-ky-nguyen-so', 'category' => 'doi-song', 'author' => $editor2,
                'title' => 'Văn hóa đọc trong kỷ nguyên số: Đọc sâu giữa dòng thông tin ngắn',
                'excerpt' => 'Sách giấy, sách điện tử và sách nói có thể cùng tồn tại nếu người đọc chủ động dành thời gian cho việc đọc có mục đích.',
                'body' => '<p>Mỗi ngày, người dùng tiếp xúc với lượng thông tin lớn qua mạng xã hội, bản tin và video ngắn. Trong bối cảnh đó, đọc sâu là khả năng tập trung với một văn bản đủ lâu để hiểu lập luận và kết nối ý tưởng.</p><h2>Tạo không gian cho sự tập trung</h2><p>Đặt mục tiêu đọc vừa sức, ghi chú câu hỏi và tắt thông báo trong khoảng thời gian ngắn có thể giúp hình thành thói quen. Không có một định dạng duy nhất phù hợp với tất cả mọi người.</p><p>Điều quan trọng là lựa chọn nguồn đáng tin cậy và dành thời gian suy nghĩ về điều đã đọc.</p>',
            ],
            [
                'slug' => 'thanh-pho-ben-vung-va-giao-thong-cong-cong', 'category' => 'the-gioi', 'author' => $editor1,
                'title' => 'Giao thông công cộng và bài toán phát triển thành phố bền vững',
                'excerpt' => 'Kết nối thuận tiện, thông tin minh bạch và khả năng tiếp cận là những yếu tố giúp người dân lựa chọn phương tiện công cộng.',
                'body' => '<p>Giao thông công cộng có thể góp phần giảm áp lực lên đường phố khi mạng lưới được quy hoạch đồng bộ và có khả năng kết nối giữa các khu vực.</p><h2>Trải nghiệm quyết định lựa chọn</h2><p>Thời gian chờ, độ tin cậy, điểm trung chuyển và khả năng tiếp cận với người khuyết tật đều ảnh hưởng đến quyết định sử dụng. Công nghệ cung cấp thông tin theo thời gian thực có thể giúp hành khách chủ động hơn.</p><p>Đầu tư vào giao thông công cộng là quá trình dài hạn, cần phối hợp giữa quy hoạch, vận hành và phản hồi của cư dân.</p>',
            ],
        ];

        $publishedArticles = [];
        foreach ($published as $index => $story) {
            $article = Article::updateOrCreate(['slug' => $story['slug']], [
                'author_id' => $story['author']->id,
                'category_id' => $categories[$story['category']]->id,
                'published_at' => now()->subDays(7 - $index)->setTime(8 + ($index % 4), 30),
                'archived_at' => null,
            ]);
            $revision = $article->revisions()->updateOrCreate(['revision_number' => 1], [
                'title' => $story['title'], 'excerpt' => $story['excerpt'], 'content_json' => null,
                'content_html' => $story['body'], 'cover_media_id' => null, 'status' => 'PUBLISHED',
                'rejection_reason' => null, 'reviewer_id' => $admin->id, 'submitted_at' => now()->subDays(8 - $index), 'reviewed_at' => now()->subDays(7 - $index),
            ]);
            $article->update(['current_published_revision_id' => $revision->id, 'working_revision_id' => null]);
            $publishedArticles[] = $article;
        }

        // A new pending revision for an already published article proves the public pointer is kept intact.
        $revisionArticle = $publishedArticles[0];
        $pendingRevision = $revisionArticle->revisions()->updateOrCreate(['revision_number' => 2], [
            'title' => 'Không gian xanh trong đô thị: Bản cập nhật đang chờ biên tập',
            'excerpt' => 'Một phiên bản mới đang được gửi để Admin kiểm tra; bài đã xuất bản vẫn giữ nguyên trên trang chủ.',
            'content_json' => null,
            'content_html' => '<p>Đây là revision thứ hai của bài viết. Bản cũ vẫn là nội dung công khai cho đến khi Admin duyệt phiên bản này.</p><h2>Nội dung bổ sung</h2><p>Phiên bản đang chờ duyệt giúp kiểm tra quy trình revision và bảo đảm độc giả không nhìn thấy thay đổi chưa được thông qua.</p>',
            'status' => 'PENDING_REVIEW', 'reviewer_id' => null, 'submitted_at' => now()->subHours(2), 'reviewed_at' => null,
        ]);
        $revisionArticle->update(['working_revision_id' => $pendingRevision->id]);

        $draftArticle = Article::updateOrCreate(['slug' => 'ban-nhap-ke-hoach-noi-dung-so'], [
            'author_id' => $editor2->id, 'category_id' => $categories['cong-nghe']->id,
            'published_at' => null, 'archived_at' => null,
        ]);
        $draft = $draftArticle->revisions()->updateOrCreate(['revision_number' => 1], [
            'title' => 'Bản nháp: Kế hoạch nội dung số cho cộng đồng', 'excerpt' => 'Bản nháp để Editor thử chức năng lưu và chỉnh sửa.',
            'content_json' => null, 'content_html' => '<p>Đây là bản nháp chưa được gửi duyệt.</p>', 'status' => 'DRAFT',
            'reviewer_id' => null, 'submitted_at' => null, 'reviewed_at' => null,
        ]);
        $draftArticle->update(['working_revision_id' => $draft->id, 'current_published_revision_id' => null]);

        $rejectedArticle = Article::updateOrCreate(['slug' => 'bai-viet-can-chinh-sua'], [
            'author_id' => $editor1->id, 'category_id' => $categories['doi-song']->id,
            'published_at' => null, 'archived_at' => null,
        ]);
        $rejected = $rejectedArticle->revisions()->updateOrCreate(['revision_number' => 1], [
            'title' => 'Bài viết cần bổ sung nguồn tham khảo', 'excerpt' => 'Dữ liệu mẫu ở trạng thái bị từ chối.',
            'content_json' => null, 'content_html' => '<p>Nội dung cần được bổ sung nguồn và biên tập lại trước khi gửi duyệt lần nữa.</p>',
            'status' => 'REJECTED', 'rejection_reason' => 'Vui lòng bổ sung nguồn tham khảo và làm rõ các số liệu đang đề cập.',
            'reviewer_id' => $admin->id, 'submitted_at' => now()->subDays(2), 'reviewed_at' => now()->subDay(),
        ]);
        $rejectedArticle->update(['working_revision_id' => $rejected->id, 'current_published_revision_id' => null]);

        $pendingNew = Article::updateOrCreate(['slug' => 'du-lich-trai-nghiem-dia-phuong'], [
            'author_id' => $editor2->id, 'category_id' => $categories['doi-song']->id,
            'published_at' => null, 'archived_at' => null,
        ]);
        $pendingNewRevision = $pendingNew->revisions()->updateOrCreate(['revision_number' => 1], [
            'title' => 'Du lịch trải nghiệm địa phương và sức sống cộng đồng', 'excerpt' => 'Bài viết mẫu mới đang chờ Admin duyệt.',
            'content_json' => null, 'content_html' => '<p>Du lịch trải nghiệm có thể kết nối du khách với văn hóa địa phương, nghề thủ công và các hoạt động cộng đồng.</p><h2>Thiết kế trải nghiệm có trách nhiệm</h2><p>Điều quan trọng là tôn trọng nhịp sống của cư dân và bảo đảm lợi ích được chia sẻ công bằng.</p>',
            'status' => 'PENDING_REVIEW', 'rejection_reason' => null, 'reviewer_id' => null,
            'submitted_at' => now()->subMinutes(35), 'reviewed_at' => null,
        ]);
        $pendingNew->update(['working_revision_id' => $pendingNewRevision->id, 'current_published_revision_id' => null]);

        $commentRows = [
            [$publishedArticles[0], $reader1, 'Cách nhìn về không gian công cộng rất đáng tham khảo.', 'APPROVED'],
            [$publishedArticles[0], $reader2, 'Mong có thêm ví dụ về cách cư dân tham gia bảo trì công viên.', 'PENDING'],
            [$publishedArticles[1], $reader3, 'Bài viết hữu ích, đặc biệt là phần chuẩn hóa quy trình.', 'APPROVED'],
            [$publishedArticles[3], $reader1, 'Tôi muốn tìm hiểu thêm về cách kiểm tra liên kết giả mạo.', 'PENDING'],
            [$publishedArticles[4], $reader2, 'Bình luận mẫu đã bị từ chối để kiểm thử bộ lọc.', 'REJECTED'],
        ];
        foreach ($commentRows as [$article, $reader, $content, $status]) {
            Comment::firstOrCreate([
                'article_id' => $article->id,
                'user_id' => $reader->id,
                'content' => $content,
            ], [
                'status' => $status,
                'moderated_by' => $status === 'PENDING' ? null : $admin->id,
                'moderated_at' => $status === 'PENDING' ? null : now()->subHours(5),
            ]);
        }
    }

    private function demoUser(string $emailKey, string $emailDefault, string $passwordKey, string $passwordDefault, string $name, string $role): User
    {
        $email = mb_strtolower((string) env($emailKey, $emailDefault));
        $password = (string) env($passwordKey, $passwordDefault);
        return User::updateOrCreate(['email' => $email], [
            'full_name' => $name,
            'display_name' => $name,
            'password' => Hash::make($password),
            'role' => $role,
            'status' => 'ACTIVE',
        ]);
    }
}
