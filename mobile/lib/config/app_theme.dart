import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AppTheme {
  // Brand Palette: Emerald & Mint Eco Aesthetics
  static const Color primaryColor = Color(0xFF059669); // Emerald 600
  static const Color primaryDark = Color(0xFF047857);  // Emerald 700
  static const Color primaryLight = Color(0xFFD1FAE5); // Emerald 100
  static const Color accentColor = Color(0xFF10B981);  // Emerald 500

  // Neutral Background & Surface
  static const Color scaffoldBg = Color(0xFFF8FAFC);  // Slate 50
  static const Color cardBg = Colors.white;
  static const Color textDark = Color(0xFF0F172A);    // Slate 900
  static const Color textMuted = Color(0xFF64748B);   // Slate 500
  static const Color borderColor = Color(0xFFE2E8F0); // Slate 200

  // Semantic Status Colors
  static const Color statusPending = Color(0xFFF59E0B);    // Amber 500
  static const Color statusPendingBg = Color(0xFFFEF3C7);  // Amber 100
  static const Color statusInProgress = Color(0xFF3B82F6); // Blue 500
  static const Color statusInProgressBg = Color(0xFFDBEAFE);// Blue 100
  static const Color statusCompleted = Color(0xFF10B981);  // Emerald 500
  static const Color statusCompletedBg = Color(0xFFD1FAE5); // Emerald 100
  static const Color statusRejected = Color(0xFFEF4444);   // Red 500
  static const Color statusRejectedBg = Color(0xFFFEE2E2);  // Red 100

  // Category Colors
  static const Color catOrganic = Color(0xFF10B981);  // Green
  static const Color catPlastic = Color(0xFF3B82F6);  // Blue
  static const Color catPaper = Color(0xFFF59E0B);    // Orange/Amber

  static ThemeData get lightTheme {
    final baseTextTheme = ThemeData.light().textTheme;

    return ThemeData(
      useMaterial3: true,
      colorScheme: ColorScheme.fromSeed(
        seedColor: primaryColor,
        primary: primaryColor,
        secondary: accentColor,
        surface: cardBg,
      ),
      scaffoldBackgroundColor: scaffoldBg,
      textTheme: GoogleFonts.interTextTheme(baseTextTheme).apply(
        bodyColor: textDark,
        displayColor: textDark,
      ),
      appBarTheme: const AppBarTheme(
        backgroundColor: Colors.white,
        foregroundColor: textDark,
        elevation: 0,
        centerTitle: true,
        titleTextStyle: TextStyle(
          color: textDark,
          fontSize: 18,
          fontWeight: FontWeight.w700,
        ),
        iconTheme: IconThemeData(color: textDark),
      ),
      cardTheme: CardThemeData(
        color: cardBg,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: borderColor, width: 1),
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: primaryColor,
          foregroundColor: Colors.white,
          elevation: 0,
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          textStyle: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: borderColor),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: borderColor),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: primaryColor, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: statusRejected),
        ),
        hintStyle: const TextStyle(color: textMuted, fontSize: 14),
      ),
    );
  }
}
