import 'package:flutter/widgets.dart';

class AppStrings {
  const AppStrings(this.locale);

  final Locale locale;

  static const delegate = _AppStringsDelegate();

  static AppStrings of(BuildContext context) {
    return Localizations.of<AppStrings>(context, AppStrings)!;
  }

  bool get isArabic => locale.languageCode == 'ar';

  static const Map<String, Map<String, String>> _values = {
    'ar': {
      'appName': 'منصة عسير',
      'tagline': 'فعاليات وخدمات وتجارب مميزة',
      'home': 'الرئيسية',
      'discover': 'استكشف',
      'chat': 'محادثة',
      'resale': 'إعادة بيع',
      'events': 'الفعاليات',
      'wallet': 'المحفظة',
      'bookings': 'الحجوزات',
      'profile': 'الملف الشخصي',
      'account': 'حسابي',
      'offers': 'العروض',
      'services': 'الخدمات',
      'places': 'الأماكن',
      'settings': 'الإعدادات',
      'search': 'ابحث عن فعالية أو خدمة',
      'today': 'فعاليات اليوم',
      'recommended': 'مقترح لك',
      'trending': 'الأكثر رواجاً',
      'upcoming': 'القادمة',
      'bestSelling': 'الأكثر مبيعاً',
      'latestExperiences': 'احدث الفعاليات والتجارب',
      'communities': 'استكشف المجتمعات',
      'nextBooking': 'إبحث عن حجزك القادم',
      'thisWeek': 'هذا الاسبوع',
      'tomorrow': 'غداً',
      'custom': 'مخصص',
      'more': 'المزيد',
      'viewAll': 'عرض الكل',
      'bookNow': 'احجز الآن',
      'login': 'تسجيل الدخول',
      'register': 'إنشاء حساب',
      'forgotPassword': 'نسيت كلمة المرور؟',
      'emailOrPhone': 'البريد أو رقم الجوال',
      'email': 'البريد الإلكتروني',
      'phone': 'رقم الجوال',
      'password': 'كلمة المرور',
      'confirmPassword': 'تأكيد كلمة المرور',
      'name': 'الاسم',
      'city': 'المدينة',
      'logout': 'تسجيل الخروج',
      'save': 'حفظ',
      'editProfile': 'تعديل البيانات',
      'changePassword': 'تغيير كلمة المرور',
      'myBookings': 'حجوزاتي',
      'myTickets': 'تذاكري',
      'usedOffers': 'العروض المستخدمة',
      'favorites': 'المفضلة',
      'notifications': 'الإشعارات',
      'trustedDevices': 'الأجهزة الموثوقة',
      'appIcon': 'أيقونة التطبيق',
      'currency': 'عملة',
      'timeFormat': 'تنسيق الوقت',
      'quickLogin': 'تسجيل الدخول السريع',
      'enabledFingerprint': 'تم تفعيل البصمة',
      'language': 'اللغة',
      'darkMode': 'الوضع الليلي',
      'loginRequired': 'سجل الدخول للمتابعة',
      'emptyWallet': 'لا توجد تذاكر بعد',
      'active': 'فعالة',
      'used': 'مستخدمة',
      'expired': 'منتهية',
      'downloadPdf': 'تحميل PDF',
      'onboarding1Title': 'اكتشف الفعاليات',
      'onboarding1Body': 'تصفح أفضل فعاليات وتجارب عسير بسهولة.',
      'onboarding2Title': 'احجز التذاكر',
      'onboarding2Body': 'اختر التذاكر وأكمل الحجز بخطوات واضحة.',
      'onboarding3Title': 'تابع العروض',
      'onboarding3Body': 'لا تفوّت العروض والتجارب المميزة.',
      'onboarding4Title': 'احتفظ بتذاكرك',
      'onboarding4Body': 'كل تذاكرك وQR داخل المحفظة.',
      'start': 'ابدأ الآن',
      'chooseLanguage': 'اختر اللغة',
      'noData': 'لا توجد بيانات حالياً',
      'retry': 'إعادة المحاولة',
      'free': 'مجاني',
      'support': 'المساعدة والدعم',
      'shareApp': 'شارك التطبيق',
      'aboutUs': 'من نحن',
      'securityReport': 'الإبلاغ عن الثغرات الأمنية',
      'rateExperience': 'قيم تجربتك',
      'auctionOffers': 'عروض مزاداتي',
      'coupons': 'القسائم',
      'livePaid': 'بث مباشر مدفوع',
      'resaleLists': 'قوائم إعادة البيع',
      'accountInfo': 'معلومات الحساب',
      'accessibility': 'ذوي الإعاقة',
      'travelers': 'المسافرون',
      'balance': 'الرصيد',
      'topUpBalance': 'شحن الرصيد',
      'myCards': 'بطاقاتي',
      'noChats': 'لا توجد محادثات حالياً',
      'resaleEmpty': 'لا توجد تذاكر متاحة لإعادة البيع',
    },
    'en': {
      'appName': 'Aseer Platform',
      'tagline': 'Events, services, and premium experiences',
      'home': 'Home',
      'discover': 'Discover',
      'chat': 'Chat',
      'resale': 'Resale',
      'events': 'Events',
      'wallet': 'Wallet',
      'bookings': 'Bookings',
      'profile': 'Profile',
      'account': 'Account',
      'offers': 'Offers',
      'services': 'Services',
      'places': 'Places',
      'settings': 'Settings',
      'search': 'Search events or services',
      'today': 'Today',
      'recommended': 'Recommended for you',
      'trending': 'Trending now',
      'upcoming': 'Upcoming',
      'bestSelling': 'Best selling',
      'latestExperiences': 'Latest experiences',
      'communities': 'Explore communities',
      'nextBooking': 'Find your next booking',
      'thisWeek': 'This week',
      'tomorrow': 'Tomorrow',
      'custom': 'Custom',
      'more': 'More',
      'viewAll': 'View all',
      'bookNow': 'Book now',
      'login': 'Login',
      'register': 'Register',
      'forgotPassword': 'Forgot password?',
      'emailOrPhone': 'Email or phone',
      'email': 'Email',
      'phone': 'Phone',
      'password': 'Password',
      'confirmPassword': 'Confirm password',
      'name': 'Name',
      'city': 'City',
      'logout': 'Logout',
      'save': 'Save',
      'editProfile': 'Edit profile',
      'changePassword': 'Change password',
      'myBookings': 'My bookings',
      'myTickets': 'My tickets',
      'usedOffers': 'Used offers',
      'favorites': 'Favorites',
      'notifications': 'Notifications',
      'trustedDevices': 'Trusted devices',
      'appIcon': 'App icon',
      'currency': 'Currency',
      'timeFormat': 'Time format',
      'quickLogin': 'Quick login',
      'enabledFingerprint': 'Fingerprint enabled',
      'language': 'Language',
      'darkMode': 'Dark mode',
      'loginRequired': 'Login to continue',
      'emptyWallet': 'No tickets yet',
      'active': 'Active',
      'used': 'Used',
      'expired': 'Expired',
      'downloadPdf': 'Download PDF',
      'onboarding1Title': 'Discover events',
      'onboarding1Body': 'Browse the best Aseer experiences with ease.',
      'onboarding2Title': 'Book tickets',
      'onboarding2Body': 'Choose tickets and complete checkout clearly.',
      'onboarding3Title': 'Follow offers',
      'onboarding3Body': 'Never miss special offers and experiences.',
      'onboarding4Title': 'Keep your tickets',
      'onboarding4Body': 'Your tickets and QR codes live in your wallet.',
      'start': 'Start now',
      'chooseLanguage': 'Choose language',
      'noData': 'No data yet',
      'retry': 'Retry',
      'free': 'Free',
      'support': 'Help and support',
      'shareApp': 'Share app',
      'aboutUs': 'About us',
      'securityReport': 'Report security issue',
      'rateExperience': 'Rate your experience',
      'auctionOffers': 'My auctions',
      'coupons': 'Coupons',
      'livePaid': 'Paid live stream',
      'resaleLists': 'Resale lists',
      'accountInfo': 'Account information',
      'accessibility': 'Accessibility',
      'travelers': 'Travelers',
      'balance': 'Balance',
      'topUpBalance': 'Top up balance',
      'myCards': 'My cards',
      'noChats': 'No chats yet',
      'resaleEmpty': 'No tickets available for resale',
    },
  };

  String t(String key) =>
      _values[locale.languageCode]?[key] ?? _values['en']![key] ?? key;

  String get appName => t('appName');
  String get tagline => t('tagline');
  String get home => t('home');
  String get discover => t('discover');
  String get chat => t('chat');
  String get resale => t('resale');
  String get search => t('search');
  String get events => t('events');
  String get wallet => t('wallet');
  String get bookings => t('bookings');
  String get profile => t('profile');
  String get account => t('account');
  String get offers => t('offers');
  String get services => t('services');
  String get places => t('places');
  String get today => t('today');
  String get bookNow => t('bookNow');
  String get login => t('login');
}

class _AppStringsDelegate extends LocalizationsDelegate<AppStrings> {
  const _AppStringsDelegate();

  @override
  bool isSupported(Locale locale) => ['ar', 'en'].contains(locale.languageCode);

  @override
  Future<AppStrings> load(Locale locale) async => AppStrings(locale);

  @override
  bool shouldReload(covariant LocalizationsDelegate<AppStrings> old) => false;
}
