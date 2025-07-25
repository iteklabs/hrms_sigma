import common from "@/common/composable/common";
import { ref } from "vue";
import { useI18n } from "vue-i18n";

const fields = () => {
    const url =
        "attendances?fields=id,xid,user_id,x_user_id,regular_hrs,status,no_of_hrs_late,no_of_hrs_undertime,reason,leave_type_id,x_leave_type_id,date,clock_in_time,clock_out_time,total_duration,user{id,xid,name,profile_image,profile_image_url},is_late,is_half_day,clock_in_date_time,clock_out_date_time,clock_in_ip_address,clock_out_ip_address, regular_ot, legal_holiday, legal_holiday_ot, special_holiday, special_holiday_ot, rest_day, rest_day_ot, rest_day_special_holiday, rest_day_special_holiday_ot, night_differential, date_out,user:designation{id,xid,name},user:location{id,xid,name}";
    const addEditUrl = "attendances";
    const { t } = useI18n();
    const hashableColumns = ["user_id", "leave_type_id"];
    const { dayjs } = common();

    const initData = {
        user_id: undefined,
        leave_type_id: undefined,
        date: dayjs().utc().format("YYYY-MM-DD"),
        clock_in_time: undefined,
        clock_out_time: undefined,
        clock_in_ip_address: "",
        clock_out_ip_address: "",
        is_late: 0,
        is_half_day: 0,
        reason: "",
        no_of_hrs_late: 0,
        no_of_hrs_undertime: 0,
        regular_hrs: 0,
        status: '',
    };

    const columns = ref([
        {
            title: t("attendance.user_id"),
            dataIndex: "user_id",
        },
        {
            title: t("attendance.date"),
            dataIndex: "date",
        },
        {
            title: t("attendance.clock_in_date_time"),
            dataIndex: "clock_in_date_time",
        },
        {
            title: t("attendance.date_out"),
            dataIndex: "date_out",
        },
        {
            title: t("attendance.clock_out_date_time"),
            dataIndex: "clock_out_date_time",
        },
        // {
        //     title: t("attendance.clock_in_ip_address"),
        //     dataIndex: "clock_in_ip_address",
        // },
        // {
        //     title: t("attendance.clock_out_ip_address"),
        //     dataIndex: "clock_out_ip_address",
        // },
        {
            title: t("attendance.total_duration"),
            dataIndex: "total_duration",
        },
        // {
        //     title: t("attendance.is_late"),
        //     dataIndex: "is_late",
        // },
        {
            title: t("attendance.regular_hrs"),
            dataIndex: "regular_hrs",
        },
        {
            title: t("attendance.no_of_hrs_late"),
            dataIndex: "no_of_hrs_late",
        },
        {
            title: t("attendance.no_of_hrs_undertime"),
            dataIndex: "no_of_hrs_undertime",
        },
        {
            title: t("attendance.regular_ot"),
            dataIndex: "regular_ot",
        },
        {
            title: t("attendance.rest_day"),
            dataIndex: "rest_day",
        },
        {
            title: t("attendance.rest_day_ot"),
            dataIndex: "rest_day_ot",
        },
        {
            title: t("attendance.night_differential"),
            dataIndex: "night_differential",
        },
        {
            title: t("attendance.legal_holiday"),
            dataIndex: "legal_holiday",
        },
        {
            title: t("attendance.legal_holiday_ot"),
            dataIndex: "legal_holiday_ot",
        },
        {
            title: t("attendance.special_holiday"),
            dataIndex: "special_holiday",
        },
        {
            title: t("attendance.special_holiday_ot"),
            dataIndex: "special_holiday_ot",
        },
        
        
        // {
        //     title: t("attendance.rest_day_special_holiday"),
        //     dataIndex: "rest_day_special_holiday",
        // },
        // {
        //     title: t("attendance.rest_day_special_holiday_ot"),
        //     dataIndex: "rest_day_special_holiday_ot",
        // },
        
        {
            title: t("common.status"),
            dataIndex: "status",
        },
    ]);

    const filterableColumns = [
        {
            key: "name",
            value: t("common.name"),
        },
    ];

    return {
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
        url,
    };
};

export default fields;
