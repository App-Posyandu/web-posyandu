import axios from "axios";
window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

import {
    Chart,
    CategoryScale,
    LinearScale,
    BarController,
    BarElement,
    LineController,
    LineElement,
    PointElement,
    ArcElement,
    PieController,
    DoughnutController,
    Title,
    Tooltip,
    Legend,
    Filler,
} from "chart.js";

Chart.register(
    CategoryScale,
    LinearScale,
    BarController,
    BarElement,
    LineController,
    LineElement,
    PointElement,
    ArcElement,
    PieController,
    DoughnutController,
    Title,
    Tooltip,
    Legend,
    Filler
);

import ChartDataLabels from "chartjs-plugin-datalabels";
Chart.register(ChartDataLabels);

window.Chart = Chart;
window.ChartDataLabels = ChartDataLabels; 

import Swal from "sweetalert2";
window.Swal = Swal;
