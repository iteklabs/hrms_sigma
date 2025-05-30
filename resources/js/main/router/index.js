import { notification } from "ant-design-vue";
import { createRouter, createWebHistory } from "vue-router";
import axios from "axios";
import { find, includes, remove, replace } from "lodash-es";
import { useAuthStore } from "../store/authStore";

import AuthRoutes from "./auth";
import DashboardRoutes from "./dashboard";
import AppreciationsRoutes from "./appreciations";
import LeavesRoutes from "./leaves";
import HolidayRoutes from "./holiday";
import AttendanceRoutes from "./attendance";
import PayrollRoutes from "./payroll";
import AssetsRoutes from "./asset";
import FormRoutes from "./forms";
import NewsRoutes from "./news";
import HrmSettingRoutes from "./hrmSettings";
import UserRoutes from "./users";
import SettingRoutes from "./settings";
import CompanyPolicyRoutes from "./companyPolicies";
import AccountRoutes from "./accounts";
import StaffBarRoutes from "./staffBar";
import AssignedSurveyRoutes from "./assignedSurvey";
import Offboardings from "./offboardings";
import LetterHeadRoutes from "./letterHead";
import { checkUserPermission } from "../../common/scripts/functions";

import FrontRoutes from "./front";

const appType = window.config.app_type;
const allActiveModules = window.config.modules;

const isAdminCompanySetupCorrect = () => {
    return true;
};

const isSuperAdminCompanySetupCorrect = () => {
    return true;
};

const router = createRouter({
    history: createWebHistory(),
    routes: [
        ...FrontRoutes,
        {
            path: "",
            redirect: "/admin/login",
        },
        ...AuthRoutes,
        ...DashboardRoutes,
        ...AppreciationsRoutes,
        ...LeavesRoutes,
        ...HolidayRoutes,
        ...AttendanceRoutes,
        ...PayrollRoutes,
        ...HrmSettingRoutes,
        ...UserRoutes,
        ...SettingRoutes,
        ...AssetsRoutes,
        ...NewsRoutes,
        ...FormRoutes,
        ...CompanyPolicyRoutes,
        ...AccountRoutes,
        ...StaffBarRoutes,
        ...AssignedSurveyRoutes,
        ...Offboardings,
        ...LetterHeadRoutes,
    ],
    scrollBehavior: () => ({ left: 0, top: 0 }),
});

// Including SuperAdmin Routes
const superadminRouteFilePath = appType == "saas" ? "superadmin" : "";
if (appType == "saas") {
    const newSuperAdminRoutePromise = import(
        `../../${superadminRouteFilePath}/router/index.js`
    );
    const newsubscriptionRoutePromise = import(
        `../../${superadminRouteFilePath}/router/admin/index.js`
    );

    Promise.all([newSuperAdminRoutePromise, newsubscriptionRoutePromise]).then(
        ([newSuperAdminRoute, newsubscriptionRoute]) => {
            newSuperAdminRoute.default.forEach((route) =>
                router.addRoute(route)
            );
            newsubscriptionRoute.default.forEach((route) =>
                router.addRoute(route)
            );
        }
    );
}

var _0x3347c9 = _0x2f40; function _0x2e65() { var _0x7e5616 = ['verify.main', 'post', '.settings.modules.index', 'host', '2005152ObmskM', 'Saas', 'value', 'split', 'catch', '208084aqFSjK', 'name', 'charAt', 'user', 'multiple_registration_modules', 'admin.login', 'front.homepage', '289247YHzDgc', '1212pXJaKP', 'meta', 'permission', 'Error!', 'admin', 'requireAuth', '.com/', 'superadmin.dashboard.index', 'verified_name', 'main_product_registered', 'is_superadmin', 'length', 'stock', '2jMzvCh', 'then', '300708QqNxdo', 'codeifly', 'Error', '1119565hmOPUh', 'superadmin', '20jeWopM', 'is_main_product_valid', '9691vfJEoW', 'forEach', 'admin.dashboard.index', 'saas', 'Modules\x20Not\x20Verified', 'location', 'bottomRight', 'https://', 'data', 'config', 'error', '34182wOUiyz', 'setup_app', '42auDlao', 'toJSON', 'appModule', 'superadmin.setup_app.index', '15CMcxlv', 'Don\x27t\x20try\x20to\x20null\x20it...\x20otherwise\x20it\x20may\x20cause\x20error\x20on\x20your\x20server.', 'admin.setup_app.index', 'push', 'requireUnauth', 'modules', 'envato', 'front', 'logoutAction', 'isLoggedIn', 'app_type', 'updateActiveModules', 'updateAppChecking', 'non-saas']; _0x2e65 = function () { return _0x7e5616; }; return _0x2e65(); } function _0x2f40(_0x48f8e3, _0x1fc661) { var _0x2e6589 = _0x2e65(); return _0x2f40 = function (_0x2f401a, _0x183396) { _0x2f401a = _0x2f401a - 0x6b; var _0x1f9ab4 = _0x2e6589[_0x2f401a]; return _0x1f9ab4; }, _0x2f40(_0x48f8e3, _0x1fc661); } (function (_0x2decb2, _0x11514d) { var _0x1b0793 = _0x2f40, _0x26df83 = _0x2decb2(); while (!![]) { try { var _0x376377 = -parseInt(_0x1b0793(0x6c)) / 0x1 * (parseInt(_0x1b0793(0x9e)) / 0x2) + parseInt(_0x1b0793(0x72)) / 0x3 * (-parseInt(_0x1b0793(0x89)) / 0x4) + -parseInt(_0x1b0793(0xa3)) / 0x5 + -parseInt(_0x1b0793(0x6e)) / 0x6 * (-parseInt(_0x1b0793(0x90)) / 0x7) + parseInt(_0x1b0793(0x84)) / 0x8 + parseInt(_0x1b0793(0xa0)) / 0x9 * (parseInt(_0x1b0793(0xa5)) / 0xa) + parseInt(_0x1b0793(0xa7)) / 0xb * (parseInt(_0x1b0793(0x91)) / 0xc); if (_0x376377 === _0x11514d) break; else _0x26df83['push'](_0x26df83['shift']()); } catch (_0x107cdd) { _0x26df83['push'](_0x26df83['shift']()); } } }(_0x2e65, 0x2b558)); const checkLogFog = (_0x5b0681, _0x1364a8, _0x4aed83, _0x3ac2cb) => { var _0x491a4b = _0x2f40, _0x4a18a1 = window[_0x491a4b(0xb0)][_0x491a4b(0x7c)] == _0x491a4b(0x7f) ? _0x491a4b(0x95) : _0x491a4b(0xa4); const _0x75412 = _0x5b0681[_0x491a4b(0x8a)][_0x491a4b(0x87)]('.'); if (_0x75412[_0x491a4b(0x9c)] > 0x0 && _0x75412[0x0] == _0x491a4b(0xa4)) { if (_0x5b0681[_0x491a4b(0x92)][_0x491a4b(0x96)] && _0x3ac2cb['isLoggedIn'] && _0x3ac2cb['user'] && !_0x3ac2cb[_0x491a4b(0x8c)][_0x491a4b(0x9b)]) _0x3ac2cb[_0x491a4b(0x7a)](), _0x4aed83({ 'name': _0x491a4b(0x8e) }); else { if (_0x5b0681[_0x491a4b(0x92)]['requireAuth'] && isSuperAdminCompanySetupCorrect() == ![] && _0x75412[0x1] != _0x491a4b(0x6d)) _0x4aed83({ 'name': _0x491a4b(0x71) }); else { if (_0x5b0681[_0x491a4b(0x92)][_0x491a4b(0x96)] && !_0x3ac2cb[_0x491a4b(0x7b)]) _0x4aed83({ 'name': _0x491a4b(0x8e) }); else _0x5b0681['meta'][_0x491a4b(0x76)] && _0x3ac2cb[_0x491a4b(0x7b)] ? _0x4aed83({ 'name': _0x491a4b(0x98) }) : _0x4aed83(); } } } else { if (_0x75412[_0x491a4b(0x9c)] > 0x0 && _0x75412[0x0] == _0x491a4b(0x95) && _0x3ac2cb && _0x3ac2cb[_0x491a4b(0x8c)] && _0x3ac2cb[_0x491a4b(0x8c)][_0x491a4b(0x9b)]) _0x4aed83({ 'name': _0x491a4b(0x98) }); else { if (_0x75412[_0x491a4b(0x9c)] > 0x0 && _0x75412[0x0] == _0x491a4b(0x95)) { if (_0x5b0681[_0x491a4b(0x92)][_0x491a4b(0x96)] && !_0x3ac2cb[_0x491a4b(0x7b)]) _0x3ac2cb['logoutAction'](), _0x4aed83({ 'name': _0x491a4b(0x8e) }); else { if (_0x5b0681[_0x491a4b(0x92)][_0x491a4b(0x96)] && isAdminCompanySetupCorrect() == ![] && _0x75412[0x1] != _0x491a4b(0x6d)) _0x4aed83({ 'name': _0x491a4b(0x74) }); else { if (_0x5b0681['meta']['requireUnauth'] && _0x3ac2cb[_0x491a4b(0x7b)]) _0x4aed83({ 'name': _0x491a4b(0xa9) }); else { if (_0x5b0681[_0x491a4b(0x8a)] == _0x4a18a1 + _0x491a4b(0x82)) _0x3ac2cb[_0x491a4b(0x7e)](![]), _0x4aed83(); else { var _0x151a59 = _0x5b0681[_0x491a4b(0x92)][_0x491a4b(0x93)]; _0x75412[0x1] == _0x491a4b(0x9d) && (_0x151a59 = replace(_0x5b0681[_0x491a4b(0x92)][_0x491a4b(0x93)](_0x5b0681), '-', '_')), !_0x5b0681[_0x491a4b(0x92)][_0x491a4b(0x93)] || checkUserPermission(_0x151a59, _0x3ac2cb['user']) ? _0x4aed83() : _0x4aed83({ 'name': 'admin.dashboard.index' }); } } } } } else _0x75412[_0x491a4b(0x9c)] > 0x0 && _0x75412[0x0] == _0x491a4b(0x79) ? _0x5b0681[_0x491a4b(0x92)][_0x491a4b(0x96)] && !_0x3ac2cb[_0x491a4b(0x7b)] ? (_0x3ac2cb['logoutAction'](), _0x4aed83({ 'name': _0x491a4b(0x8f) })) : _0x4aed83() : _0x4aed83(); } } }; var mAry = ['r', 'H', 'm', 'l', 'i', 'f', 'y'], mainProductName = '' + mAry[0x1] + mAry[0x0] + mAry[0x2] + mAry[0x4] + mAry[0x5] + mAry[0x3] + mAry[0x6]; window[_0x3347c9(0xb0)][_0x3347c9(0x7c)] == _0x3347c9(0xaa) && (mainProductName += _0x3347c9(0x85)); var modArray = [{ 'verified_name': mainProductName, 'value': ![] }]; allActiveModules[_0x3347c9(0xa8)](_0x3ec0d3 => { var _0x3d646f = _0x3347c9; modArray[_0x3d646f(0x75)]({ 'verified_name': _0x3ec0d3, 'value': ![] }); }); const isAnyModuleNotVerified = () => { var _0x494691 = _0x3347c9; return find(modArray, [_0x494691(0x86), ![]]); }, isCheckUrlValid = (_0x126119, _0x2d5e7b, _0x1007e4) => { var _0x23f7fe = _0x3347c9; if (_0x126119['length'] != 0x5 || _0x2d5e7b[_0x23f7fe(0x9c)] != 0x8 || _0x1007e4[_0x23f7fe(0x9c)] != 0x6) return ![]; else { if (_0x126119[_0x23f7fe(0x8b)](0x3) != 'c' || _0x126119[_0x23f7fe(0x8b)](0x4) != 'k' || _0x126119[_0x23f7fe(0x8b)](0x0) != 'c' || _0x126119[_0x23f7fe(0x8b)](0x1) != 'h' || _0x126119[_0x23f7fe(0x8b)](0x2) != 'e') return ![]; else { if (_0x2d5e7b[_0x23f7fe(0x8b)](0x2) != 'd' || _0x2d5e7b[_0x23f7fe(0x8b)](0x3) != 'e' || _0x2d5e7b[_0x23f7fe(0x8b)](0x4) != 'i' || _0x2d5e7b[_0x23f7fe(0x8b)](0x0) != 'c' || _0x2d5e7b[_0x23f7fe(0x8b)](0x1) != 'o' || _0x2d5e7b[_0x23f7fe(0x8b)](0x5) != 'f' || _0x2d5e7b['charAt'](0x6) != 'l' || _0x2d5e7b[_0x23f7fe(0x8b)](0x7) != 'y') return ![]; else return _0x1007e4[_0x23f7fe(0x8b)](0x2) != 'v' || _0x1007e4[_0x23f7fe(0x8b)](0x3) != 'a' || _0x1007e4[_0x23f7fe(0x8b)](0x0) != 'e' || _0x1007e4[_0x23f7fe(0x8b)](0x1) != 'n' || _0x1007e4[_0x23f7fe(0x8b)](0x4) != 't' || _0x1007e4[_0x23f7fe(0x8b)](0x5) != 'o' ? ![] : !![]; } } }, isAxiosResponseUrlValid = _0x5896bd => { var _0x366097 = _0x3347c9; return _0x5896bd['charAt'](0x13) != 'i' || _0x5896bd[_0x366097(0x8b)](0xd) != 'o' || _0x5896bd[_0x366097(0x8b)](0x9) != 'n' || _0x5896bd['charAt'](0x10) != 'o' || _0x5896bd['charAt'](0x16) != 'y' || _0x5896bd[_0x366097(0x8b)](0xb) != 'a' || _0x5896bd[_0x366097(0x8b)](0x12) != 'e' || _0x5896bd[_0x366097(0x8b)](0x15) != 'l' || _0x5896bd[_0x366097(0x8b)](0xa) != 'v' || _0x5896bd[_0x366097(0x8b)](0x14) != 'f' || _0x5896bd['charAt'](0xc) != 't' || _0x5896bd[_0x366097(0x8b)](0x11) != 'd' || _0x5896bd[_0x366097(0x8b)](0x8) != 'e' || _0x5896bd[_0x366097(0x8b)](0xf) != 'c' || _0x5896bd[_0x366097(0x8b)](0x1a) != 'm' || _0x5896bd[_0x366097(0x8b)](0x18) != 'c' || _0x5896bd[_0x366097(0x8b)](0x19) != 'o' ? ![] : !![]; }; router['beforeEach']((_0x7933c8, _0x2111fb, _0x154a80) => { var _0x4d5d09 = _0x3347c9; const _0x4ad19a = useAuthStore(); var _0x3a91ec = _0x4d5d09(0x78), _0xb33758 = _0x4d5d09(0xa1), _0x41dd2c = 'check', _0x502e25 = { 'modules': window[_0x4d5d09(0xb0)][_0x4d5d09(0x77)] }; _0x7933c8[_0x4d5d09(0x92)] && _0x7933c8[_0x4d5d09(0x92)][_0x4d5d09(0x70)] && (_0x502e25['module'] = _0x7933c8['meta']['appModule'], !includes(allActiveModules, _0x7933c8[_0x4d5d09(0x92)][_0x4d5d09(0x70)]) && _0x154a80({ 'name': 'admin.login' })); if (!isCheckUrlValid(_0x41dd2c, _0xb33758, _0x3a91ec)) Modal[_0x4d5d09(0x6b)]({ 'title': _0x4d5d09(0x94), 'content': _0x4d5d09(0x73) }); else { var _0x4ad7a1 = window[_0x4d5d09(0xb0)][_0x4d5d09(0x7c)] == _0x4d5d09(0x7f) ? _0x4d5d09(0x95) : _0x4d5d09(0xa4); if (isAnyModuleNotVerified() !== undefined && _0x7933c8[_0x4d5d09(0x8a)] && _0x7933c8[_0x4d5d09(0x8a)] != _0x4d5d09(0x80) && _0x7933c8[_0x4d5d09(0x8a)] != _0x4ad7a1 + '.settings.modules.index') { var _0x4fe7ca = _0x4d5d09(0xae) + _0x3a91ec + '.' + _0xb33758 + _0x4d5d09(0x97) + _0x41dd2c; axios({ 'method': _0x4d5d09(0x81), 'url': _0x4fe7ca, 'data': { 'verified_name': mainProductName, ..._0x502e25, 'domain': window[_0x4d5d09(0xac)][_0x4d5d09(0x83)] }, 'timeout': 0xfa0 })[_0x4d5d09(0x9f)](_0x1f366c => { var _0x5b8c4b = _0x4d5d09; if (!isAxiosResponseUrlValid(_0x1f366c['config']['url'])) Modal[_0x5b8c4b(0x6b)]({ 'title': _0x5b8c4b(0x94), 'content': _0x5b8c4b(0x73) }); else { _0x4ad19a[_0x5b8c4b(0x7e)](![]); const _0x58460e = _0x1f366c[_0x5b8c4b(0xaf)]; _0x58460e[_0x5b8c4b(0x9a)] && (modArray[_0x5b8c4b(0xa8)](_0x28ca93 => { var _0x47370b = _0x5b8c4b; _0x28ca93['verified_name'] == mainProductName && (_0x28ca93[_0x47370b(0x86)] = !![]); }), modArray[_0x5b8c4b(0xa8)](_0x44a513 => { var _0x59931e = _0x5b8c4b; if (includes(_0x58460e['modules_not_registered'], _0x44a513[_0x59931e(0x99)]) || includes(_0x58460e[_0x59931e(0x8d)], _0x44a513[_0x59931e(0x99)])) { if (_0x44a513[_0x59931e(0x99)] != mainProductName) { var _0x4c13dd = [...window['config'][_0x59931e(0x77)]], _0x35fec7 = remove(_0x4c13dd, function (_0x225bd5) { return _0x225bd5 != _0x44a513['verified_name']; }); _0x4ad19a[_0x59931e(0x7d)](_0x35fec7), window[_0x59931e(0xb0)][_0x59931e(0x77)] = _0x35fec7; } _0x44a513[_0x59931e(0x86)] = ![]; } else _0x44a513[_0x59931e(0x86)] = !![]; })); if (!_0x58460e[_0x5b8c4b(0xa6)]) { } else { if (!_0x58460e[_0x5b8c4b(0x9a)] || _0x58460e['multiple_registration']) _0x154a80({ 'name': 'verify.main' }); else { if (_0x7933c8[_0x5b8c4b(0x92)] && _0x7933c8[_0x5b8c4b(0x92)]['appModule'] && find(modArray, { 'verified_name': _0x7933c8['meta'][_0x5b8c4b(0x70)], 'value': ![] }) !== undefined) { notification[_0x5b8c4b(0x6b)]({ 'placement': _0x5b8c4b(0xad), 'message': _0x5b8c4b(0xa2), 'description': _0x5b8c4b(0xab) }); const _0x4746cf = appType == _0x5b8c4b(0xaa) ? _0x5b8c4b(0xa4) : _0x5b8c4b(0x95); _0x154a80({ 'name': _0x4746cf + _0x5b8c4b(0x82) }); } else checkLogFog(_0x7933c8, _0x2111fb, _0x154a80, _0x4ad19a); } } } })[_0x4d5d09(0x88)](_0x51bb4f => { var _0x162e1b = _0x4d5d09; !isAxiosResponseUrlValid(_0x51bb4f[_0x162e1b(0x6f)]()['config']['url']) ? Modal['error']({ 'title': _0x162e1b(0x94), 'content': 'Don\x27t\x20try\x20to\x20null\x20it...\x20otherwise\x20it\x20may\x20cause\x20error\x20on\x20your\x20server.' }) : (modArray[_0x162e1b(0xa8)](_0x2b19e8 => { var _0xc83bd3 = _0x162e1b; _0x2b19e8[_0xc83bd3(0x86)] = !![]; }), _0x4ad19a[_0x162e1b(0x7e)](![]), _0x154a80()); }); } else checkLogFog(_0x7933c8, _0x2111fb, _0x154a80, _0x4ad19a); } });


export default router;
