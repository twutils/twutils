<?php

return [
    'brand_desc' => 'تويتلز مجموعة من الخدمات المُساعِدة لإدارة حسابك في تويتر.',

    'task_add_max_number'          => 'لقد وصلت إلى الحد الأقصى من عدد المهمات الممكن استخدامها لحسابك',
    'task_add_target_not_found'    => 'المهمة المُنشأة سابقاً لتكون المصدر المزوِّد لهذه المهمة، غير متاحة',
    'task_add_no_privilege'        => 'هذه المهمة تتطلَّب إتاحة مستوى صلاحية أعلى لـTwUtils على حسابك في تويتر.',
    'task_add_bad_request'         => 'فشل في تحديد نوع المهمة المطلوبة',
    'task_add_unauthorized_access' => 'لا تملك صلاحية استخدام هذه المهمة',

    'socialauth_canceled' => 'تم منعنا من تلقي الإجابة من تويتر',
    'deleteMe_canceled'   => 'ممتاز! تم إلغاء طلب حذف حسابك في تويتلز',
    'deleteMe_pending'    => 'هناك طلب مسبق مُعلَّق لحذف حسابك، قم بإلغاؤه أولاً.',

    'twitter_account'     => 'حساب تويتر',
    'twitter_connections' => 'الاتصال بتويتر',

    'danger_zone'        => 'منطقة الخطر',
    'deleteMe'           => 'احذف حسابي',
    'deleteMe_desc'      => 'احذف حسابي (في تويتلز، وليس في تويتر) وكل المهام المتعلقة به. يمكنك تحديد متى ترغب بحذف حسابك أو الحذف فوراً، في حال الحذف فوراً، هذا الإجراء لا يمكن التراجع عنه.',
    'deleteMe_guide'     => 'حدِّد متى ترغب أن يتم حذف حسابك. اترك الحقول فارغة للحذف فوراً.',
    'accountToBeRemoved' => 'سيتم حذف حسابك',

    'privilege'     => 'الصلاحية',
    'read'          => 'قراءة',
    'write'         => 'كتابة',
    'add'           => 'إضافة',
    'revoke_access' => 'إلغاء الصلاحية',
    'activity'      => 'النشاط',
    'registered'    => 'مسجَّل',
    'last_login'    => 'آخر تسجيل دخول',

    'home'       => 'الرئيسية',
    'profile'    => 'العضوية',
    'twitter'    => 'تويتر',
    'login'      => 'تسجيل دخول',
    'login_with' => 'سجّل دخول عبر',
    'will_start' => 'سوف يبدأ تلقائياً بعد',
    'seconds'    => 'ثانية',
    'logout'     => 'تسجيل خروج',

    'page' => 'صفحة',

    'explore'   => 'تصفّح',
    'dashboard' => 'لوحة التحكم',

    'startTask' => 'بدء المهمة..',

    'confirmDeleteMe' => 'هل أنت متأكد من رغبتك بحذف حسابك؟ لن يمكنك التراجع عن هذا الإجراء',

    'goto_home' => 'توجّه إلى الصفحة الرئيسية!',
    'features'  => 'الخدمات',

    'call_to_action_desc' => 'قم بتسجيل الدخول عبر تويتر لتصفح الخدمات التي يمكنك الاستفادة منها.',
    'task'                => 'المهمة',
    'tasks'               => 'المهام',
    'status'              => 'الحالة',
    'created_at'          => 'تاريخ الإنشاء',
    'updated_at'          => 'تاريخ التحديث',
    'details'             => 'تفاصيل',
    'no_tasks'            => 'ليست هناك مهام، بعد ..',

    'backup' => 'نسخ',
    'remove' => 'حذف',
    'cancel' => 'إلغاء',

    'download_likes'      => 'تحميل المفضلة',
    'download_likes_desc' => 'قم بتحميل نسخة ملف إكسل تحتوي على قائمة المفضلة الخاصة بك',

    'likes'                      => 'المفضلات',
    'backup_likes'               => 'نسخ المفضلة',
    'backup_likes_desc'          => 'اجلُب نسخة من مفضلتك الحالية.',
    'backup_likes_entities'      => 'نسخ المفضلة (مع الوسائط)',
    'backup_likes_entities_desc' => 'اجلُب نسخة من مفضلتك الحالية مع الوسائط.',
    'user_tweets'                => 'نسخ التغريدات',
    'user_tweets_desc'           => 'اجلُب نسخة من تغريداتك الحالية.',
    'destroy_likes'              => 'حذف المفضلة',
    'destroy_likes_desc'         => 'قُم بحذف قائمة المفضلة بالكامل',
    'destroy_tweets'             => 'حذف التغريدات',
    'destroy_tweets_desc'        => 'قم بحذف تغريداتك.',

    'removedLikes'  => 'التغريدات المفضّلة المحذوفة',
    'removedTweets' => 'التغريدات المحذوفة',

    'download'                                                             => 'تحميل',
    'completed'                                                            => 'مكتملة',
    'staging'                                                              => 'تقريباً مكتملة',
    'queued'                                                               => 'في الطريق',
    'broken'                                                               => 'معطّلة',
    \App\TwUtils\TwitterOperations\FetchLikesOperation::class              => 'نسخ المفضلة',
    \App\TwUtils\TwitterOperations\FetchEntitiesLikesOperation::class      => 'نسخ المفضلة (مع الوسائط)',
    \App\TwUtils\TwitterOperations\FetchEntitiesUserTweetsOperation::class => 'نسخ التغريدات (مع الوسائط)',
    \App\TwUtils\TwitterOperations\FetchUserTweetsOperation::class         => 'نسخ التغريدات',
    \App\TwUtils\TwitterOperations\FetchFollowingOperation::class          => 'نسخ المُتابَعين',
    \App\TwUtils\TwitterOperations\FetchFollowersOperation::class          => 'نسخ المُتابِعين',
    \App\TwUtils\TwitterOperations\DestroyLikesOperation::class            => 'حذف المفضلة',
    \App\TwUtils\TwitterOperations\DestroyTweetsOperation::class           => 'حذف التغريدات',
    \App\TwUtils\TwitterOperations\ManagedDestroyLikesOperation::class     => 'حذف المفضلة',
    \App\TwUtils\TwitterOperations\ManagedDestroyTweetsOperation::class    => 'حذف التغريدات',

    'fetch_following'      => 'نسخ قائمة المُتابَعين',
    'fetch_following_desc' => 'نسخ قائمة المُتابَعين الحالية',

    'following' => 'مُتابَع',

    'fetch_followers'      => 'نسخ قائمة المُتابِعين',
    'fetch_followers_desc' => 'نسخ قائمة المُتابِعين الحالية',

    'followers' => 'مُتابِع',

    'tweet'  => 'تغريدة',
    'tweets' => 'تغريدات',

    'chose'                => 'اختيار',
    'adding_backup_likes'  => 'إضافة مهمة نسخ المفضلة، بانتظار الاستجابة...',
    'ongoing_backup_likes' => 'هناك مهمة نسخ للمفضلة لا زالت جارية، برجاء الانتظار حتى الانتهاء منها أولاً.',

    'adding_user_tweets'  => 'إضافة مهمة نسخ التغريدات، بانتظار الاستجابة...',
    'ongoing_user_tweets' => 'هناك مهمة نسخ التغريدات لا زالت جارية، برجاء الانتظار حتى الانتهاء منها أولاً.',

    'adding_fetch_following'  => 'إضافة مهمة نسخ قائمة المُتابعة، بانتظار الاستجابة...',
    'ongoing_fetch_following' => 'هناك مهمة نسخ لقائمة المُتابَعين لا زالت جارية، برجاء الانتظار حتى الانتهاء منها أولاً.',

    'adding_fetch_followers'  => 'إضافة مهمة نسخ قائمة المتابعين بانتظار الاستجابة...',
    'ongoing_fetch_followers' => 'هناك مهمة نسخ لقائمة المُتابِعين لا زالت جارية، برجاء الانتظار حتى الانتهاء منها أولاً.',

    'loading_destroy_likes'  => 'حذف المفضلة. بانتظار الاستجابة',
    'ongoing_destroy_likes'  => 'هناك مهمة حذف للمفضلة لا زالت جارية، برجاء الانتظار حتى الانتهاء منها أولاً.',
    'loading_destroy_tweets' => 'حذف التغريدات، بانتظار الاستجابة',
    'ongoing_destroy_tweets' => 'هناك مهمة حذف للتغريدات لا زالت جارية، برجاء الانتظار حتى الانتهاء منها أولاً.',

    'close' => 'إغلاق',

    'name'    => 'الاسم',
    'email'   => 'البريد الالكتروني',
    'purpose' => 'بخصوص',
    'message' => 'الرسالة',
    'send'    => 'إرسال',

    'contact_us'                     => 'تواصل معنا',
    'contact_us_desc'                => 'سنحب أن نسمع ما لديك!',
    'contact_us_additional_channels' => 'أيضاً، يمكنك التواصل معنا عبر أحد القنوات التالية:',

    'suggestion'     => 'اقتراح',
    'feedback'       => 'تسجيل انطباع (Feedback)',
    'ux_improvement' => 'تحسين تجربة المستخدم',
    'report_a_bug'   => 'إبلاغ عن خلل/ثغرة',
    'support'        => 'مساعدة',
    'other'          => 'آخر',

    'go_to_details'     => 'اضغط للتفاصيل',
    'no_previous_tasks' => 'لا توجد مهام سابقة',
    'history'           => 'المهام السابقة',
    'options'           => 'الخيارات',
    'with_media'        => 'مع الوسائط',

    'total_users'    => 'كل المستخدمين',
    'sorted_by'      => 'مرتبة بحسب',
    'search_results' => 'نتائج البحث',
    'per_page'       => 'في الصفحة الواحدة',
    'search'         => 'بحث',
    'ascending'      => 'تصاعدي',
    'descending'     => 'تنازلي',

    'selected_tweets_source'      => 'مصدر التغريدات المُختار',
    'selected_tweets_source_desc' => 'لحذف التغريدات نحتاج نسخ حالتها الحالية من حسابك أولاً. للقيام بذلك، قام النظام بإنشاء مهمة نسخ لها. ليقوم بعد ذلك باستخدام هذه المهمة كمصدر للحذف.',
    'start_date'                  => 'تاريخ البداية',
    'end_date'                    => 'تاريخ النهاية',
    'removed'                     => 'المحذوف',
];
