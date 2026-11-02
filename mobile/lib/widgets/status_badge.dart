import 'package:flutter/material.dart';
import 'package:smart_waste_mobile/config/app_constants.dart';
import 'package:smart_waste_mobile/config/app_theme.dart';

class StatusBadge extends StatelessWidget {
  final String status;
  final bool isCompact;

  const StatusBadge({
    super.key,
    required this.status,
    this.isCompact = false,
  });

  @override
  Widget build(BuildContext context) {
    Color bg;
    Color fg;
    String label = AppConstants.statusLabelsId[status] ?? status;

    switch (status) {
      case AppConstants.statusPending:
        bg = AppTheme.statusPendingBg;
        fg = AppTheme.statusPending;
        break;
      case AppConstants.statusInProgress:
        bg = AppTheme.statusInProgressBg;
        fg = AppTheme.statusInProgress;
        break;
      case AppConstants.statusCompleted:
        bg = AppTheme.statusCompletedBg;
        fg = AppTheme.statusCompleted;
        break;
      case AppConstants.statusRejected:
        bg = AppTheme.statusRejectedBg;
        fg = AppTheme.statusRejected;
        break;
      default:
        bg = const Color(0xFFF1F5F9);
        fg = const Color(0xFF64748B);
    }

    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: isCompact ? 8 : 12,
        vertical: isCompact ? 4 : 6,
      ),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 6,
            height: 6,
            decoration: BoxDecoration(color: fg, shape: BoxShape.circle),
          ),
          const SizedBox(width: 6),
          Text(
            label,
            style: TextStyle(
              color: fg,
              fontSize: isCompact ? 11 : 12,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }
}
