import common from "@/common/composable/common";
import { ref } from "vue";
import { useI18n } from "vue-i18n";

const fields = () => {
    const url =
        "attendances_upload?fields=id,xid,user_id,x_user_id,date,date_to,time_in,time_out,schedule_type,x_schedule_location_id,schedule_location_id,user{id,xid,name,profile_image,profile_image_url},scheduleLocation{id,xid,name}";
    const addEditUrl = "attendances_upload";
    const { t } = useI18n();
    const hashableColumns = ["user_id", "schedule_location_id"];
    const { dayjs } = common();

    const initData = {
        xid: '',
        user_id: undefined,
        date: '',
        date_to: '',
        time_in: "",
        time_out: "",
        schedule_type: "",
        schedule_location_id: undefined,
    };

    const columns = ref([
        {
            title: 'Employee',
            dataIndex: "user_id",
        },
        {
            title: 'Date From',
            dataIndex: "date",
        },
        {
            title:  'Date to',
            dataIndex: "date_to",
        },
        {
            title: 'In',
            dataIndex: "time_in",
        },
        {
            title: 'Out',
            dataIndex: "time_out",
        },
        {
            title: 'Shift Type',
            dataIndex: "schedule_type",
        },
        {
            title: 'Location/Detachment',
            dataIndex: "schedule_location_id",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ]);


    const columns_data = ref([
        {
            title: 'Employee',
            dataIndex: "employee_name",
        },
        {
            title: 'Date From',
            dataIndex: "date",
        },
        {
            title:  'Date to',
            dataIndex: "date_to",
        },
        {
            title: 'In',
            dataIndex: "time_in",
        },
        {
            title: 'Out',
            dataIndex: "time_out",
        },
        {
            title: 'Shift Type',
            dataIndex: "schedule_type",
        },
        {
            title: 'Location/Detachment',
            dataIndex: "location_name",
        },
        
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
        columns_data
    };
};

export default fields;