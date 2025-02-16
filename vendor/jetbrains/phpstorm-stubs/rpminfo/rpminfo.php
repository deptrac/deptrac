<?php

namespace DEPTRAC_INTERNAL;

\define('RPMVERSION', '4.15.1');
\define('RPMSENSE_ANY', 0);
\define('RPMSENSE_LESS', 2);
\define('RPMSENSE_GREATER', 4);
\define('RPMSENSE_EQUAL', 8);
\define('RPMSENSE_POSTTRANS', 32);
\define('RPMSENSE_PREREQ', 64);
\define('RPMSENSE_PRETRANS', 128);
\define('RPMSENSE_INTERP', 256);
\define('RPMSENSE_SCRIPT_PRE', 512);
\define('RPMSENSE_SCRIPT_POST', 1024);
\define('RPMSENSE_SCRIPT_PREUN', 2048);
\define('RPMSENSE_SCRIPT_POSTUN', 4096);
\define('RPMSENSE_SCRIPT_VERIFY', 8192);
\define('RPMSENSE_FIND_REQUIRES', 16384);
\define('RPMSENSE_FIND_PROVIDES', 32768);
\define('RPMSENSE_TRIGGERIN', 65536);
\define('RPMSENSE_TRIGGERUN', 131072);
\define('RPMSENSE_TRIGGERPOSTUN', 262144);
\define('RPMSENSE_MISSINGOK', 524288);
\define('RPMSENSE_RPMLIB', 16777216);
\define('RPMSENSE_TRIGGERPREIN', 33554432);
\define('RPMSENSE_KEYRING', 67108864);
\define('RPMSENSE_CONFIG', 268435456);
\define('RPMMIRE_DEFAULT', 0);
\define('RPMMIRE_STRCMP', 1);
\define('RPMMIRE_REGEX', 2);
\define('RPMMIRE_GLOB', 3);
\define('RPMTAG_ARCH', 1022);
\define('RPMTAG_ARCHIVESIZE', 1046);
\define('RPMTAG_BASENAMES', 1117);
\define('RPMTAG_BUGURL', 5012);
\define('RPMTAG_BUILDARCHS', 1089);
\define('RPMTAG_BUILDHOST', 1007);
\define('RPMTAG_BUILDTIME', 1006);
\define('RPMTAG_C', 1054);
\define('RPMTAG_CHANGELOGNAME', 1081);
\define('RPMTAG_CHANGELOGTEXT', 1082);
\define('RPMTAG_CHANGELOGTIME', 1080);
\define('RPMTAG_CLASSDICT', 1142);
\define('RPMTAG_CONFLICTFLAGS', 1053);
\define('RPMTAG_CONFLICTNAME', 1054);
\define('RPMTAG_CONFLICTNEVRS', 5044);
\define('RPMTAG_CONFLICTS', 1054);
\define('RPMTAG_CONFLICTVERSION', 1055);
\define('RPMTAG_COOKIE', 1094);
\define('RPMTAG_DBINSTANCE', 1195);
\define('RPMTAG_DEPENDSDICT', 1145);
\define('RPMTAG_DESCRIPTION', 1005);
\define('RPMTAG_DIRINDEXES', 1116);
\define('RPMTAG_DIRNAMES', 1118);
\define('RPMTAG_DISTRIBUTION', 1010);
\define('RPMTAG_DISTTAG', 1155);
\define('RPMTAG_DISTURL', 1123);
\define('RPMTAG_DSAHEADER', 267);
\define('RPMTAG_E', 1003);
\define('RPMTAG_ENCODING', 5062);
\define('RPMTAG_ENHANCEFLAGS', 5057);
\define('RPMTAG_ENHANCENAME', 5055);
\define('RPMTAG_ENHANCENEVRS', 5061);
\define('RPMTAG_ENHANCES', 5055);
\define('RPMTAG_ENHANCEVERSION', 5056);
\define('RPMTAG_EPOCH', 1003);
\define('RPMTAG_EPOCHNUM', 5019);
\define('RPMTAG_EVR', 5013);
\define('RPMTAG_EXCLUDEARCH', 1059);
\define('RPMTAG_EXCLUDEOS', 1060);
\define('RPMTAG_EXCLUSIVEARCH', 1061);
\define('RPMTAG_EXCLUSIVEOS', 1062);
\define('RPMTAG_FILECAPS', 5010);
\define('RPMTAG_FILECLASS', 1141);
\define('RPMTAG_FILECOLORS', 1140);
\define('RPMTAG_FILECONTEXTS', 1147);
\define('RPMTAG_FILEDEPENDSN', 1144);
\define('RPMTAG_FILEDEPENDSX', 1143);
\define('RPMTAG_FILEDEVICES', 1095);
\define('RPMTAG_FILEDIGESTALGO', 5011);
\define('RPMTAG_FILEDIGESTS', 1035);
\define('RPMTAG_FILEFLAGS', 1037);
\define('RPMTAG_FILEGROUPNAME', 1040);
\define('RPMTAG_FILEINODES', 1096);
\define('RPMTAG_FILELANGS', 1097);
\define('RPMTAG_FILELINKTOS', 1036);
\define('RPMTAG_FILEMD5S', 1035);
\define('RPMTAG_FILEMODES', 1030);
\define('RPMTAG_FILEMTIMES', 1034);
\define('RPMTAG_FILENAMES', 5000);
\define('RPMTAG_FILENLINKS', 5045);
\define('RPMTAG_FILEPROVIDE', 5001);
\define('RPMTAG_FILERDEVS', 1033);
\define('RPMTAG_FILEREQUIRE', 5002);
\define('RPMTAG_FILESIGNATURELENGTH', 5091);
\define('RPMTAG_FILESIGNATURES', 5090);
\define('RPMTAG_FILESIZES', 1028);
\define('RPMTAG_FILESTATES', 1029);
\define('RPMTAG_FILETRIGGERCONDS', 5086);
\define('RPMTAG_FILETRIGGERFLAGS', 5072);
\define('RPMTAG_FILETRIGGERINDEX', 5070);
\define('RPMTAG_FILETRIGGERNAME', 5069);
\define('RPMTAG_FILETRIGGERPRIORITIES', 5084);
\define('RPMTAG_FILETRIGGERSCRIPTFLAGS', 5068);
\define('RPMTAG_FILETRIGGERSCRIPTPROG', 5067);
\define('RPMTAG_FILETRIGGERSCRIPTS', 5066);
\define('RPMTAG_FILETRIGGERTYPE', 5087);
\define('RPMTAG_FILETRIGGERVERSION', 5071);
\define('RPMTAG_FILEUSERNAME', 1039);
\define('RPMTAG_FILEVERIFYFLAGS', 1045);
\define('RPMTAG_FSCONTEXTS', 1148);
\define('RPMTAG_GIF', 1012);
\define('RPMTAG_GROUP', 1016);
\define('RPMTAG_HDRID', 269);
\define('RPMTAG_HEADERCOLOR', 5017);
\define('RPMTAG_HEADERI18NTABLE', 100);
\define('RPMTAG_HEADERIMAGE', 61);
\define('RPMTAG_HEADERIMMUTABLE', 63);
\define('RPMTAG_HEADERREGIONS', 64);
\define('RPMTAG_HEADERSIGNATURES', 62);
\define('RPMTAG_ICON', 1043);
\define('RPMTAG_INSTALLCOLOR', 1127);
\define('RPMTAG_INSTALLTID', 1128);
\define('RPMTAG_INSTALLTIME', 1008);
\define('RPMTAG_INSTFILENAMES', 5040);
\define('RPMTAG_INSTPREFIXES', 1099);
\define('RPMTAG_LICENSE', 1014);
\define('RPMTAG_LONGARCHIVESIZE', 271);
\define('RPMTAG_LONGFILESIZES', 5008);
\define('RPMTAG_LONGSIGSIZE', 270);
\define('RPMTAG_LONGSIZE', 5009);
\define('RPMTAG_MODULARITYLABEL', 5096);
\define('RPMTAG_N', 1000);
\define('RPMTAG_NAME', 1000);
\define('RPMTAG_NEVR', 5015);
\define('RPMTAG_NEVRA', 5016);
\define('RPMTAG_NOPATCH', 1052);
\define('RPMTAG_NOSOURCE', 1051);
\define('RPMTAG_NVR', 5014);
\define('RPMTAG_NVRA', 1196);
\define('RPMTAG_O', 1090);
\define('RPMTAG_OBSOLETEFLAGS', 1114);
\define('RPMTAG_OBSOLETENAME', 1090);
\define('RPMTAG_OBSOLETENEVRS', 5043);
\define('RPMTAG_OBSOLETES', 1090);
\define('RPMTAG_OBSOLETEVERSION', 1115);
\define('RPMTAG_OLDENHANCES', 1159);
\define('RPMTAG_OLDENHANCESFLAGS', 1161);
\define('RPMTAG_OLDENHANCESNAME', 1159);
\define('RPMTAG_OLDENHANCESVERSION', 1160);
\define('RPMTAG_OLDFILENAMES', 1027);
\define('RPMTAG_OLDSUGGESTS', 1156);
\define('RPMTAG_OLDSUGGESTSFLAGS', 1158);
\define('RPMTAG_OLDSUGGESTSNAME', 1156);
\define('RPMTAG_OLDSUGGESTSVERSION', 1157);
\define('RPMTAG_OPTFLAGS', 1122);
\define('RPMTAG_ORDERFLAGS', 5037);
\define('RPMTAG_ORDERNAME', 5035);
\define('RPMTAG_ORDERVERSION', 5036);
\define('RPMTAG_ORIGBASENAMES', 1120);
\define('RPMTAG_ORIGDIRINDEXES', 1119);
\define('RPMTAG_ORIGDIRNAMES', 1121);
\define('RPMTAG_ORIGFILENAMES', 5007);
\define('RPMTAG_OS', 1021);
\define('RPMTAG_P', 1047);
\define('RPMTAG_PACKAGER', 1015);
\define('RPMTAG_PATCH', 1019);
\define('RPMTAG_PATCHESFLAGS', 1134);
\define('RPMTAG_PATCHESNAME', 1133);
\define('RPMTAG_PATCHESVERSION', 1135);
\define('RPMTAG_PAYLOADCOMPRESSOR', 1125);
\define('RPMTAG_PAYLOADDIGEST', 5092);
\define('RPMTAG_PAYLOADDIGESTALGO', 5093);
\define('RPMTAG_PAYLOADFLAGS', 1126);
\define('RPMTAG_PAYLOADFORMAT', 1124);
\define('RPMTAG_PKGID', 261);
\define('RPMTAG_PLATFORM', 1132);
\define('RPMTAG_POLICIES', 1150);
\define('RPMTAG_POLICYFLAGS', 5033);
\define('RPMTAG_POLICYNAMES', 5030);
\define('RPMTAG_POLICYTYPES', 5031);
\define('RPMTAG_POLICYTYPESINDEXES', 5032);
\define('RPMTAG_POSTIN', 1024);
\define('RPMTAG_POSTINFLAGS', 5021);
\define('RPMTAG_POSTINPROG', 1086);
\define('RPMTAG_POSTTRANS', 1152);
\define('RPMTAG_POSTTRANSFLAGS', 5025);
\define('RPMTAG_POSTTRANSPROG', 1154);
\define('RPMTAG_POSTUN', 1026);
\define('RPMTAG_POSTUNFLAGS', 5023);
\define('RPMTAG_POSTUNPROG', 1088);
\define('RPMTAG_PREFIXES', 1098);
\define('RPMTAG_PREIN', 1023);
\define('RPMTAG_PREINFLAGS', 5020);
\define('RPMTAG_PREINPROG', 1085);
\define('RPMTAG_PRETRANS', 1151);
\define('RPMTAG_PRETRANSFLAGS', 5024);
\define('RPMTAG_PRETRANSPROG', 1153);
\define('RPMTAG_PREUN', 1025);
\define('RPMTAG_PREUNFLAGS', 5022);
\define('RPMTAG_PREUNPROG', 1087);
\define('RPMTAG_PROVIDEFLAGS', 1112);
\define('RPMTAG_PROVIDENAME', 1047);
\define('RPMTAG_PROVIDENEVRS', 5042);
\define('RPMTAG_PROVIDES', 1047);
\define('RPMTAG_PROVIDEVERSION', 1113);
\define('RPMTAG_PUBKEYS', 266);
\define('RPMTAG_R', 1002);
\define('RPMTAG_RECOMMENDFLAGS', 5048);
\define('RPMTAG_RECOMMENDNAME', 5046);
\define('RPMTAG_RECOMMENDNEVRS', 5058);
\define('RPMTAG_RECOMMENDS', 5046);
\define('RPMTAG_RECOMMENDVERSION', 5047);
\define('RPMTAG_RECONTEXTS', 1149);
\define('RPMTAG_RELEASE', 1002);
\define('RPMTAG_REMOVETID', 1129);
\define('RPMTAG_REQUIREFLAGS', 1048);
\define('RPMTAG_REQUIRENAME', 1049);
\define('RPMTAG_REQUIRENEVRS', 5041);
\define('RPMTAG_REQUIRES', 1049);
\define('RPMTAG_REQUIREVERSION', 1050);
\define('RPMTAG_RPMVERSION', 1064);
\define('RPMTAG_RSAHEADER', 268);
\define('RPMTAG_SHA1HEADER', 269);
\define('RPMTAG_SHA256HEADER', 273);
\define('RPMTAG_SIGGPG', 262);
\define('RPMTAG_SIGMD5', 261);
\define('RPMTAG_SIGPGP', 259);
\define('RPMTAG_SIGSIZE', 257);
\define('RPMTAG_SIZE', 1009);
\define('RPMTAG_SOURCE', 1018);
\define('RPMTAG_SOURCEPACKAGE', 1106);
\define('RPMTAG_SOURCEPKGID', 1146);
\define('RPMTAG_SOURCERPM', 1044);
\define('RPMTAG_SUGGESTFLAGS', 5051);
\define('RPMTAG_SUGGESTNAME', 5049);
\define('RPMTAG_SUGGESTNEVRS', 5059);
\define('RPMTAG_SUGGESTS', 5049);
\define('RPMTAG_SUGGESTVERSION', 5050);
\define('RPMTAG_SUMMARY', 1004);
\define('RPMTAG_SUPPLEMENTFLAGS', 5054);
\define('RPMTAG_SUPPLEMENTNAME', 5052);
\define('RPMTAG_SUPPLEMENTNEVRS', 5060);
\define('RPMTAG_SUPPLEMENTS', 5052);
\define('RPMTAG_SUPPLEMENTVERSION', 5053);
\define('RPMTAG_TRANSFILETRIGGERCONDS', 5088);
\define('RPMTAG_TRANSFILETRIGGERFLAGS', 5082);
\define('RPMTAG_TRANSFILETRIGGERINDEX', 5080);
\define('RPMTAG_TRANSFILETRIGGERNAME', 5079);
\define('RPMTAG_TRANSFILETRIGGERPRIORITIES', 5085);
\define('RPMTAG_TRANSFILETRIGGERSCRIPTFLAGS', 5078);
\define('RPMTAG_TRANSFILETRIGGERSCRIPTPROG', 5077);
\define('RPMTAG_TRANSFILETRIGGERSCRIPTS', 5076);
\define('RPMTAG_TRANSFILETRIGGERTYPE', 5089);
\define('RPMTAG_TRANSFILETRIGGERVERSION', 5081);
\define('RPMTAG_TRIGGERCONDS', 5005);
\define('RPMTAG_TRIGGERFLAGS', 1068);
\define('RPMTAG_TRIGGERINDEX', 1069);
\define('RPMTAG_TRIGGERNAME', 1066);
\define('RPMTAG_TRIGGERSCRIPTFLAGS', 5027);
\define('RPMTAG_TRIGGERSCRIPTPROG', 1092);
\define('RPMTAG_TRIGGERSCRIPTS', 1065);
\define('RPMTAG_TRIGGERTYPE', 5006);
\define('RPMTAG_TRIGGERVERSION', 1067);
\define('RPMTAG_URL', 1020);
\define('RPMTAG_V', 1001);
\define('RPMTAG_VCS', 5034);
\define('RPMTAG_VENDOR', 1011);
\define('RPMTAG_VERBOSE', 5018);
\define('RPMTAG_VERIFYSCRIPT', 1079);
\define('RPMTAG_VERIFYSCRIPTFLAGS', 5026);
\define('RPMTAG_VERIFYSCRIPTPROG', 1091);
\define('RPMTAG_VERSION', 1001);
\define('RPMTAG_XPM', 1013);
/**
 * Compare two RPM evr (epoch:version-release) strings
 *
 * @param string $evr1 <p>
 * First epoch:version-release string
 * </p>
 * @param string $evr2 <p>
 * Second epoch:version-release string
 * </p>
 * @return int <p>
 * < 0 if evr1 < evr2, > 0 if evr1 > evr2, 0 if equal.
 * </p>
 * @since 0.1.0
 */
function rpmvercmp(string $evr1, string $evr2)
{
}
/**
 * Retrieve information from a RPM file, reading its metadata.
 * If given error will be used to store error message
 * instead of raising a warning. The return
 * value is a hash table,
 * or false if it fails.
 *
 * @param string $path <p>
 * Path to the RPM file.
 * </p>
 * @param bool $full [optional] <p>
 * If TRUE all information headers for the file are retrieved, else only a minimal set.
 * </p>
 * @param null|string &$error [optional] <p>
 * If provided, will receive the possible error message, and will avoid a runtime warning.
 * </p>
 *
 * @return array|null <p>
 * An array of information or <b>NULL</b> on error.
 * </p>
 * @since 0.1.0
 */
function rpminfo(string $path, bool $full = \false, ?string &$error = null)
{
}
/**
 * Retrieve information about an installed package, from the system RPM database.
 *
 * @param string $nevr <p>
 * Name with optional epoch, version and release.
 * </p>
 * @param bool $full [optional] <p>
 * If TRUE all information headers for the file are retrieved, else only a minimal set.
 * </p>
 *
 * @return array|null <p>
 * An array of arrays of information or <b>NULL</b> on error.
 * </p>
 * @since 0.2.0
 */
function rpmdbinfo(string $nevr, bool $full = \false)
{
}
/**
 * Retriev information from the local RPM database.
 *
 * @param string $pattern <p>
 * Value to search for.
 * </p>
 * @param int $rpmtag [optional] <p>
 * Search criterion, one of RPMTAG_* constant.
 * </p>
 * @param int $rpmmire [optional] <p>
 * Pattern type, one of RPMMIRE_* constant.
 * When < 0 the criterion must equals the value, and database index is used if possible.
 * </p>
 * @param bool $full [optional] <p>
 * If TRUE all information headers for the file are retrieved, else only a minimal set.
 * </p>
 *
 * @return array|null <p>
 * An array of arrays of information or <b>NULL</b> on error.
 * </p>
 * @since 0.3.0
 */
function rpmdbsearch(string $pattern, int $rpmtag = \RPMTAG_NAME, int $rpmmire = -1, bool $full = \false)
{
}
/**
 * Add an additional retrieved tag in subsequent queries.
 *
 * @param int $tag One of RPMTAG_* constant, see the rpminfo constants page.
 *
 * @return bool Returns true on success or false on failure.
 * @since 0.5.0
 */
function rpmaddtag(int $tag) : bool
{
}
