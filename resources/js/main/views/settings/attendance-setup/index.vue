<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.attendance_setup`)" class="p-0">
            </a-page-header>
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t("menu.settings") }}
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t("menu.attendance_setup") }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <a-row>
        <a-col
            :xs="24"
            :sm="24"
            :md="24"
            :lg="4"
            :xl="4"
            :style="{
                background: themeMode == 'dark' ? '#141414' : '#fff',
                borderRight:
                    themeMode == 'dark' ? '1px solid #303030' : '1px solid #f0f0f0',
            }"
        >
            <SettingSidebar />
        </a-col>
        <a-col :xs="24" :sm="24" :md="24" :lg="20" :xl="20">
            <admin-page-table-content>
                <a-card class="page-content-container mt-20 mb-20">
                    <a-form layout="vertical">
                        <a-tabs v-model:activeKey="activeKey">
                            <a-tab-pane key="general_settings">
                                <template #tab>
                                    <span>
                                        <SettingOutlined />
                                        {{ $t("company.general_settings") }}
                                    </span>
                                </template>
                                

                                <!-- Custom Attendance Setup Fields -->
                                <a-row :gutter="16">
                                    <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                        <a-form-item label="Total Hrs Per Day" name="total_hrs_per_day">
                                            <a-input-number
                                                v-model:value="formData.total_hrs_per_day"
                                                :min="0"
                                                :max="24"
                                                :step="0.01"
                                                style="width: 100%"
                                                placeholder="e.g. 8"
                                            />
                                        </a-form-item>
                                    </a-col>
                                    <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                        <a-form-item label="Regular OT Percentage" name="regular_ot_percentage">
                                            <a-input-number
                                                v-model:value="formData.regular_ot_percentage"
                                                :min="0"
                                                :max="1000"
                                                :step="0.01"
                                                style="width: 100%"
                                                placeholder="e.g. 125"
                                                addon-after="%"
                                            />
                                        </a-form-item>
                                    </a-col>
                                    <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                        <a-form-item label="Legal Holiday Percentage" name="legal_holiday_percentage">
                                            <a-input-number
                                                v-model:value="formData.legal_holiday_percentage"
                                                :min="0"
                                                :max="1000"
                                                :step="0.01"
                                                style="width: 100%"
                                                placeholder="e.g. 200"
                                                addon-after="%"
                                            />
                                        </a-form-item>
                                    </a-col>
                                </a-row>
                                <a-row :gutter="16">
                                    <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                        <a-form-item label="Legal Holiday OT Percentage" name="legal_holiday_ot_percentage">
                                            <a-input-number
                                                v-model:value="formData.legal_holiday_ot_percentage"
                                                :min="0"
                                                :max="1000"
                                                :step="0.01"
                                                style="width: 100%"
                                                placeholder="e.g. 260"
                                                addon-after="%"
                                            />
                                        </a-form-item>
                                    </a-col>
                                    <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                        <a-form-item label="Rest Day Percentage" name="rest_day_percentage">
                                            <a-input-number
                                                v-model:value="formData.rest_day_percentage"
                                                :min="0"
                                                :max="1000"
                                                :step="0.01"
                                                style="width: 100%"
                                                placeholder="e.g. 130"
                                                addon-after="%"
                                            />
                                        </a-form-item>
                                    </a-col>
                                    <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                        <a-form-item label="Rest Day OT Percentage" name="rest_day_ot_percentage">
                                            <a-input-number
                                                v-model:value="formData.rest_day_ot_percentage"
                                                :min="0"
                                                :max="1000"
                                                :step="0.01"
                                                style="width: 100%"
                                                placeholder="e.g. 169"
                                                addon-after="%"
                                            />
                                        </a-form-item>
                                    </a-col>
                                </a-row>
                                <a-row :gutter="16">
                                    <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                        <a-form-item label="Night Differential Percentage" name="night_diff_percentage">
                                            <a-input-number
                                                v-model:value="formData.night_diff_percentage"
                                                :min="0"
                                                :max="1000"
                                                :step="0.01"
                                                style="width: 100%"
                                                placeholder="e.g. 110"
                                                addon-after="%"
                                            />
                                        </a-form-item>
                                    </a-col>
                                    <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                        <a-form-item label="Night Differential Start Time" name="night_diff_start">
                                            <a-time-picker
                                                v-model:value="formData.night_diff_start_time"
                                                format="HH:mm"
                                                value-format="HH:mm"
                                                style="width: 100%"
                                                placeholder="e.g. 22:00"
                                                :show-now="false"
                                            />
                                        </a-form-item>
                                    </a-col>
                                    <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                        <a-form-item label="Night Differential End Time" name="night_diff_end">
                                            <a-time-picker
                                                v-model:value="formData.night_diff_end_time"
                                                format="HH:mm"
                                                value-format="HH:mm"
                                                style="width: 100%"
                                                placeholder="e.g. 06:00"
                                                :show-now="false"
                                            />
                                        </a-form-item>
                                    </a-col>
                                </a-row>
                                <!-- End Custom Attendance Setup Fields -->

                            </a-tab-pane>
                        </a-tabs>
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="24" :md="24" :lg="24">
                                <a-form-item>
                                    <a-button
                                        type="primary"
                                        @click="onSubmit"
                                        :loading="loading"
                                    >
                                        <template #icon>
                                            <SaveOutlined />
                                        </template>
                                        {{ $t("common.update") }}
                                    </a-button>
                                </a-form-item>
                            </a-col>
                        </a-row>
                    </a-form>
                </a-card>
            </admin-page-table-content>
        </a-col>
    </a-row>
</template>
<script>
import {
ApartmentOutlined,
ControlOutlined,
DeleteOutlined,
EditOutlined,
ExclamationCircleOutlined,
ExperimentOutlined,
EyeOutlined,
FileTextOutlined,
PlusOutlined,
SaveOutlined,
SettingOutlined,
} from "@ant-design/icons-vue";
import { onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import { ColorPicker } from "vue3-colorpicker";
import "vue3-colorpicker/style.css";
import apiAdmin from "../../../../common/composable/apiAdmin";
import common from "../../../../common/composable/common";
import Upload from "../../../../common/core/ui/file/Upload.vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import { useAuthStore } from "../../../store/authStore";
import CurrencyAddButton from "../../common/settings/currency/AddButton.vue";
import PdfFontSettings from "../../settings/pdf-fonts/PdfFontSettings.vue";
import SettingSidebar from "../SettingSidebar.vue";

export default {
    components: {
        EyeOutlined,
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        ExclamationCircleOutlined,
        SaveOutlined,
        SettingOutlined,
        FileTextOutlined,
        ExperimentOutlined,
        ApartmentOutlined,
        ControlOutlined,
        ColorPicker,

        Upload,
        CurrencyAddButton,
        SettingSidebar,
        AdminPageHeader,
        PdfFontSettings,
    },
    setup() {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const { permsArray, appSetting, dayjsObject, appType, themeMode } = common();
        const { t } = useI18n();
        const formData = ref({});
        const currencies = ref([]);
        const timezones = ref([]);
        const dateFormats = ref([]);
        const timeFormats = ref([]);
        const company = appSetting.value;
        const currencyUrl = "currencies?limit=10000";
        const timezoneUrl = "timezones";
        const gradientColor = ref(
            "linear-gradient(0deg, rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 1) 100%)"
        );
        const activeKey = ref("general_settings");
        const appThemeMode = window.config.theme_mode;
        const visible = ref(false);
        const authStore = useAuthStore();

        onMounted(() => {
            const currencyPromise = axiosAdmin.get(currencyUrl);
            const timezonePromise = axiosAdmin.get(timezoneUrl);

            Promise.all([currencyPromise, timezonePromise]).then(
                ([currenciesResponse, timezonesResponse]) => {
                    currencies.value = currenciesResponse.data;
                    timezones.value = timezonesResponse.data.timezones;
                    dateFormats.value = timezonesResponse.data.date_formates;
                    timeFormats.value = timezonesResponse.data.time_formates;

                    setFormData();
                }
            );
        });

        const pdfCustomFonts = () => {
            visible.value = true;
        };

        const setFormData = () => {
            formData.value = {
                name: company.name,
                short_name: company.short_name,
                email: company.email,
                phone: company.phone,
                address: company.address,
                left_sidebar_theme: company.left_sidebar_theme,
                dark_logo: company.dark_logo,
                dark_logo_url: company.dark_logo_url,
                light_logo: company.light_logo,
                light_logo_url: company.light_logo_url,
                small_dark_logo: company.small_dark_logo,
                small_light_logo: company.small_light_logo,
                small_dark_logo_url: company.small_dark_logo_url,
                small_light_logo_url: company.small_light_logo_url,
                login_image: company.login_image,
                login_image_url: company.login_image_url,
                shortcut_menus: company.shortcut_menus,
                rtl: company.rtl,
                currency_id: company.x_currency_id,
                primary_color: company.primary_color,
                timezone: company.timezone,
                date_format: company.date_format,
                time_format: company.time_format,
                auto_detect_timezone: company.auto_detect_timezone,
                app_debug: company.app_debug,
                update_app_notification: company.update_app_notification,
                regular_ot_percentage: company.regular_ot_percentage,
                legal_holiday_percentage: company.legal_holiday_percentage,
                legal_holiday_ot_percentage: company.legal_holiday_ot_percentage,
                rest_day_percentage: company.rest_day_percentage,
                rest_day_ot_percentage: company.rest_day_ot_percentage,
                night_diff_percentage: company.night_diff_percentage,
                night_diff_start_time: company.night_diff_start_time,
                night_diff_end_time: company.night_diff_end_time,
                total_hrs_per_day: company.total_hrs_per_day,
                _method: "PUT",
            };
        };

        const onSubmit = () => {
            addEditRequestAdmin({
                url: `companies/${company.xid}`,
                data: formData.value,
                successMessage: t("company.updated"),
                success: (res) => {
                    authStore.updateAppAction();
                },
            });
        };

        const currencyAdded = () => {
            axiosAdmin.get(currencyUrl).then((response) => {
                currencies.value = response.data;
            });
        };

        const addMenuSettingUpdated = (menuPosition) => {
            formData.value.shortcut_menus = menuPosition;
        };

        const onClose = () => {
            visible.value = false;
        };

        return {
            appType,
            permsArray,
            formData,
            loading,
            rules,
            currencies,
            onSubmit,
            currencyAdded,
            gradientColor,
            timezones,
            dateFormats,
            timeFormats,
            dayjsObject,
            appThemeMode,
            addMenuSettingUpdated,
            themeMode,
            activeKey,
            visible,
            pdfCustomFonts,
            onClose,
        };
    },
};
</script>
