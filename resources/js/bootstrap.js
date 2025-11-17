import axios from "axios";
window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Import Chart.js
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

// Register Chart.js components
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

// Import Chart.js Plugin Datalabels
import ChartDataLabels from "chartjs-plugin-datalabels";
Chart.register(ChartDataLabels);

// Make Chart available globally
window.Chart = Chart;
window.ChartDataLabels = ChartDataLabels; 

// Import SweetAlert2
import Swal from "sweetalert2";
window.Swal = Swal;
